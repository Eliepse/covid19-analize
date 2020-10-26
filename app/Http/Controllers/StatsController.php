<?php


namespace App\Http\Controllers;


class StatsController
{
	public function __invoke(string $country = null)
	{
		return view('country', ["country" => $country]);
	}
}
