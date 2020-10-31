<?php
/** @noinspection PhpMissingFieldTypeInspection */

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SyncDataCommand extends Command
{
    protected $signature = 'sync {--force}';
    protected $description = 'Load data from WHO';
    private string $url = "https://covid19.who.int/WHO-COVID-19-global-data.csv";

    public function handle(): int
    {
	    $this->info("Checking if data has been updated...");
        $ch = curl_init($this->url);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($ch);
        curl_close($ch);

        if (!preg_match("/^last-modified:(.+)$/m", $output, $matches)) {
            $this->error("Cannot read the date from header.");
            return 1;
        }

        $lastModifiedAt = Carbon::createFromFormat(Carbon::RFC7231, trim($matches[1] ?? ""));
        $lastUpdatedAt = Cache::get("previous_file_date");

        if (!$this->option("force") && !is_null($lastUpdatedAt) && !$lastModifiedAt->isAfter($lastUpdatedAt)) {
            $this->info("Data is up to date.");
            return 0;
        }

	    $this->info("Dowloading data...");
        $ch = curl_init($this->url);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_NOBODY, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $lines = explode("\n", curl_exec($ch));
        curl_close($ch);

        array_shift($lines);

	    $this->info("Saving data...");
        DB::table("cases")->truncate();

        foreach (array_chunk($lines, 300) as $chunk) {
            $bulk = [];
            foreach ($chunk as $line) {
                if (empty($line)) continue;
                $data = str_getcsv($line);
                $bulk[] = [
                    "country" => $data[2],
                    "new_cases" => $data[4],
                    "reported_at" => $data[0],
                ];
            }
            DB::table("cases")->insert($bulk);
        }

	    Cache::forever("previous_file_date", $lastModifiedAt);

	    $this->info("Synchronization complete!");

        $this->info("All new cases : " . number_format(DB::table("cases")->sum("new_cases"), 0, ',', ' '));
        $this->info("France new cases : " . number_format(DB::table("cases")->where("country", "France")->sum("new_cases"), 0, ',', ' '));

        return 0;
    }
}
