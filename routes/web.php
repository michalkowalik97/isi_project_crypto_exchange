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

Route::group(['middleware' => ['auth']], function () {

    Route::resource('/', 'DashboardController');
    Route::get('/logs','LogsController@index');
    Route::get('/logs/download/{filename}','LogsController@download');

    //account
    Route::prefix('/account/settings')->group(function () {

        Route::get('/', 'AccountController@index');

        Route::get('/change/password', 'AccountController@editPassword');
        Route::post('/change/password', 'AccountController@updatePassword');

        Route::delete('integration', 'IntegrationController@destroy');

        Route::resource('f2a', 'GaController');
        Route::resource('integration', 'IntegrationController');
    });

    Route::post('/select/market', 'ExchangeController@selectMarket');


    Route::prefix('/exchange')->group(function () {

        Route::get('/update/markets', 'ExchangeController@updateAvailableMarkets');

        Route::get('/{selected?}', 'ExchangeController@index');
        Route::get('get/orderbook/{market}/{visible?}', 'ExchangeController@getOrderbook');


        Route::post('/offer/{market}/buy', 'ExchangeController@buy');
        Route::post('/offer/{market}/sell', 'ExchangeController@sell');


        Route::get('/offers/active', 'OffersController@getActiveList');
        Route::get('/offers/history', 'OffersController@getHistoryList');
        Route::get('/offers/cancel/{id}', 'OffersController@destroy');
    });

    Route::prefix('/wallets')->group(function () {
        Route::get('/paypal/{id}','WalletController@paypal');
        Route::post('/paypal','WalletController@paypalStore');
    });
    Route::resource('wallets', 'WalletController');


    Route::prefix('/bot')->group(function () {
        Route::get('/jobs', 'BotController@jobs');
        Route::get('/stats','BotController@stats');
        Route::get('/jobs/new', 'BotController@create');
        Route::get('/jobs/{id}','BotController@show');
        Route::get('/jobs/{id}/edit','BotController@edit');
        Route::get('/jobs/{id}/toggle/active','BotController@toggleActive');

        Route::put('/jobs/{id}','BotController@update');
        Route::post('/jobs','BotController@store');
    });

    Route::resource('bot', 'BotController');

    Route::get('/test','DashboardController@test');
});


Auth::routes();
Route::get('login/google', 'Auth\LoginController@redirectToProvider');
Route::get('callback', 'Auth\LoginController@handleProviderCallback');

Route::get('/cron/stonks/maker','BotController@cronStonksMaker');
Route::get('/exchange/offers/check','ExchangeController@checkOffers');

/*Route::get('/home', function (){
    return redirect('/');
})->name('home');*/
