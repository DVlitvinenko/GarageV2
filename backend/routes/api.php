<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\FileController;
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
    Route::get('auth/cars', [CarsController::class, 'GetCars']);
    Route::post('auth/cars/booking', [CarsController::class, 'Booking']);
    Route::post('auth/cars/cancel-booking', [CarsController::class, 'cancelBooking']);
    Route::post('user/logout', [AuthController::class, 'logout']);
    Route::get('user', [AuthController::class, 'GetUser']);
    Route::post('upload-file', [FileController::class, 'uploadFile']);
});
Route::get('cars', [CarsController::class, 'GetCars']);
Route::post('user/login', [AuthController::class, 'loginOrRegister']);
Route::post('user/code', [AuthController::class, 'CreateAndSendCode']);
