<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use  App\Http\Controllers\UserAuthController;
use App\Http\Controllers\NbpController;
use  App\Http\Controllers\SubscribedCurrencyController;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
//Route::get('/controller',[NbpController::class,'update_currncy_table']);


Route::middleware('auth:sanctum')->prefix('/user')->group(function() {

  
    Route::post('/subscribed_currencies',[SubscribedCurrencyController::class,'store']);
    Route::delete('/subscribed_currencies/{currency_name}',[SubscribedCurrencyController::class,'delete']);
    Route::delete('/subscribed_currencies',[SubscribedCurrencyController::class,'delete_all']);
    Route::get('/subscribed_currencies',[SubscribedCurrencyController::class,'index']);

    Route::get('/subscribed_currencies/available',[NbpController::class,'available_currencies']);
  
});

Route::prefix('/user')->group(function() {

  
    Route::post('/register',[UserAuthController::class,'register']);
    Route::post('/login',[UserAuthController::class,'login']);

});