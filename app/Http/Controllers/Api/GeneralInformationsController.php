<?php


namespace App\Http\Controllers\Api;


use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class GeneralInformationsController
{
    public function __invoke(Request $request, string $country = null)
    {
        $query = DB::table("cases");

        if (!empty($country = trim($country))) {
            $query->where("country", $country);
        }


        $new_cases = $query->clone()->groupBy("reported_at")
            ->selectRaw("SUM(new_cases) AS new_cases, reported_at")
            ->whereDate("reported_at", ">=", Carbon::today()->subMonths(6))
            ->get()
            ->each(fn($val) => $val->new_cases = intval($val->new_cases));

        $smooth = $this->getAverage($new_cases, 5);
        $smoother = $this->getAverage($smooth, 5);

        return [
            "total" => [
                "cases" => intval($query->sum("new_cases")),
            ],
            "data" => [
                "raw" => $new_cases->pluck("new_cases", "reported_at"),
                "smooth" => $smooth->pluck("new_cases", "reported_at"),
                "smoother" => $smoother->pluck("new_cases", "reported_at"),
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
                if ($cases = $data[$j]->new_cases ?? null) {
                    $buffer[] = $cases;
                }
            }
            $day = clone $data[$i];
            $day->new_cases = round(count($buffer) === 0 ? 0 : array_sum($buffer) / count($buffer));
            $smoothed[$i] = $day;
        }

        return $smoothed;
    }
}
