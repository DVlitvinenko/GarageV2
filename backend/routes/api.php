<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\CarsController;

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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });
// Route::post('user/register', [AuthController::class, 'register']);
// Route::post('user/login', [AuthController::class, 'login'])->name('login');
Route::middleware('auth:sanctum')->get('auth/cars', [CarsController::class, 'GetCars']);
Route::middleware('auth:sanctum')->post('auth/cars/booking', [CarsController::class, 'Booking']);
Route::middleware('auth:sanctum')->post('auth/cars/cancel-booking', [CarsController::class, 'cancelBooking']);
Route::get('cars', [CarsController::class, 'GetCars']);
Route::get('user', [AuthController::class, 'loginOrRegister']);
Route::post('user/code', [AuthController::class, 'CreateAndSendCode']);
Route::post('user/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::middleware('auth:sanctum')->post('upload-file', [FileController::class, 'uploadFile']);
