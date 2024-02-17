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

if (env('APP_ENV') === 'production') {
    URL::forceScheme('https');
}
// Route::get('/', function () {
//     return redirect()->route('home');
// });



Route::middleware('api.key')->post('/cars', [APIController::class, 'pushCars']);
Route::middleware('api.key')->put('/cars', [APIController::class, 'updateCar']);
Route::middleware('api.key')->put('/cars/rent-term', [APIController::class, 'updateCarRentTerm']);
Route::middleware('api.key')->put('/cars/status', [APIController::class, 'updateCarStatus']);
Route::middleware('api.key')->post('/parks/rent-terms', [APIController::class, 'createOrUpdateRentTerm']);
Route::middleware('api.key')->put('/parks', [APIController::class, 'updateParkInfo']);
Route::middleware('api.key')->post('/parks/division', [APIController::class, 'createParkDivision']);
Route::middleware('api.key')->post('/parks/tariff', [APIController::class, 'createOrUpdateTariff']);
Route::middleware('api.key')->put('/parks/division', [APIController::class, 'updateParkDivision']);
Route::middleware('api.key')->put('/cars/booking', [APIController::class, 'updateCarBookingStatus']);



// Auth::routes();

// Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');


Route::group(['prefix' => 'admin'], function () {
    Voyager::routes();
});
