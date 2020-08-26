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

    Route::resource('/','DashboardController');


    //account
    Route::prefix('/account/settings')->group(function (){

        Route::get('/', 'AccountController@index');

        Route::get('/change/password','AccountController@editPassword');
        Route::post('/change/password','AccountController@updatePassword');

        Route::delete('integration', 'IntegrationController@destroy' );

        Route::resource('f2a', 'GaController');
        Route::resource('integration', 'IntegrationController');
    });

    Route::post('/select/market', 'ExchangeController@selectMarket');


    Route::prefix('/exchange')->group(function (){

        Route::get('/update/markets','ExchangeController@updateAvailableMarkets');

        Route::get('/{selected?}', 'ExchangeController@index');
      //  Route::resource('/', 'ExchangeController');

    });


    Route::prefix('/wallets')->group(function (){

        Route::resource('/', 'WalletController');
    });


});



Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
