<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;

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
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::middleware('auth:sanctum')->get('/user-name', [AuthController::class, 'getSession']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

// "access_token": "1|vynLWb2gOiMpic0aLgoEPmfwdpARiQPXjYn3DiKl74225261"
