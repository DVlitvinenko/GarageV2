<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\APIController;
use Illuminate\Support\Facades\URL;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

URL::forceScheme('https');
// Route::get('/', function () {
//     return redirect()->route('home');
// });



Route::post('/cars', [APIController::class, 'pushCars'])->middleware('api.key');
Route::put('/cars', [APIController::class, 'updateCar'])->middleware('api.key');
Route::put('/cars/rent-term', [APIController::class, 'UpdateCarRentTerm'])->middleware('api.key');
Route::put('/cars/status', [APIController::class, 'updateCarStatus'])->middleware('api.key');
Route::post('/parks/rent-terms', [APIController::class, 'createOrUpdateRentTerm'])->middleware('api.key');



// Auth::routes();

// Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');


Route::group(['prefix' => 'admin'], function () {
    Voyager::routes();
});
