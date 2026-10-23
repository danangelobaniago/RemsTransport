<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Background location from the Traccar Client app on a driver's phone.
// Authenticated by the driver's secret tracking token (the app's device id).
Route::match(['get', 'post'], '/driver-tracking', [\App\Http\Controllers\DriverTrackingController::class, 'store'])
    ->middleware('throttle:240,1')
    ->name('driver.tracking');
