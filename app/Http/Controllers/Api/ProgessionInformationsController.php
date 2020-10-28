<?php


namespace App\Http\Controllers\Api;


use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use stdClass;

class ProgessionInformationsController
{
    public function __invoke(Request $request, string $country = null)
    {
        $query = DB::table("cases");

        if (!empty($country = trim($country))) {
            $query->where("country", $country);
        }

        $new_cases = $query->clone()->groupBy("reported_at")
            ->selectRaw("SUM(new_cases) AS new_cases, reported_at")
            ->get()
            ->each(fn($val) => $val->new_cases = intval($val->new_cases));

        $cases_difference = collect();

        for ($i = 0; $i < $new_cases->count(); $i++) {
            $current = $new_cases->get($i);
            $previous = $new_cases->get($i - 1);
            $diff = new stdClass();
            $diff->reported_at = $current->reported_at;
            if (empty($previous) || empty($previous->new_cases)) {
                $diff->value = null;
                $cases_difference->put($i, $diff);
                continue;
            }
            $diff->value = max(min((($current->new_cases / $previous->new_cases) * 100) - 100, 100), -100);
            $cases_difference->put($i, $diff);
        }

        for ($i = 1; $i < $cases_difference->count() - 1; $i++) {
            $current = $cases_difference->get($i);
            if (!empty($current->value)) {
                continue;
            }
            $previous = $cases_difference->get($i - 1);
            $next = $cases_difference->get($i + 1);
            $current->value = ($previous->value + $next->value) / 2;
        }

        $smooth = $this->getAverage($cases_difference, 5);
        $smoother = $this->getAverage($smooth, 5);

        return [
            "total" => [
                "cases" => intval($query->sum("new_cases")),
            ],
            "data" => [
                "raw" => $cases_difference->pluck("value", "reported_at"),
                "smooth" => $smooth->pluck("value", "reported_at"),
                "smoother" => $smoother->pluck("value", "reported_at"),
            ]
        ];
    }

    private function getAverage(Collection $data, int $days_around = 3): Collection
    {
        $smoothed = collect();
        $count = $data->count();

        for ($i = 0; $i < $count; $i++) {
            $range = [$i - $days_around, $i + $days_around];
            $buffer = [];
            for ($j = $range[0]; $j < $range[1]; $j++) {
                if ($cases = $data[$j]->value ?? null) {
                    $buffer[] = $cases;
                }
            }
            $day = clone $data[$i];
            $day->value = round(count($buffer) === 0 ? 0 : array_sum($buffer) / count($buffer));
            $smoothed[$i] = $day;
        }

        return $smoothed;
    }
}
