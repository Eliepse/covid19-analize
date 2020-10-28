<?php

use App\Http\Controllers\Api\GeneralInformationsController;
use App\Http\Controllers\Api\ProgessionInformationsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('/general/{country?}', GeneralInformationsController::class);
Route::get('/progression/{country?}', ProgessionInformationsController::class);
