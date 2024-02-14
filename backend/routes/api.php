<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\CarsController;
use PHPUnit\Framework\Attributes\Group;

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

Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::post('auth/cars/booking', [CarsController::class, 'Booking']);
    Route::post('auth/cars/cancel-booking', [CarsController::class, 'cancelBooking']);
    Route::post('user/logout', [AuthController::class, 'logout']);
    Route::delete('user', [AuthController::class, 'DeleteUser']);
    Route::get('user', [AuthController::class, 'GetUser']);
    Route::post('driver/upload-file', [DriverController::class, 'uploadDocs']);
});
Route::post('cars/filter', [CarsController::class, 'GetCars']);
Route::post('cars/search', [CarsController::class, 'SearchCars']);
Route::get('car', [CarsController::class, 'GetCar']);
Route::post('user/login', [AuthController::class, 'loginOrRegister']);
Route::post('user/code', [AuthController::class, 'CreateAndSendCode']);
