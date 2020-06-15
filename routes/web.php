<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::group(['middleware' => ['auth']], function(){
    Route::get('/', function () {
        return view('dashboard.index');
    });

    //account
    Route::prefix('/account/settings')->group(function (){

        Route::get('/', 'AccountController@index');

        Route::resource('f2a', 'GaController');

        Route::resource('integration', 'IntegrationController');
        Route::delete('integration', 'IntegrationController@destroy' );



    });

    Route::post('/select/market', 'ExchangeController@selectMarket');
    Route::resource('exchange', 'ExchangeController');

});



Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
