<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/', 'BaseController@index')->name('index');

Route::group(['prefix' => 'location', 'namespace' => 'Location'], function () {
    Route::group(['prefix' => 'get'], function () {
        Route::get('/country', 'LocationController@getCountry')->name('getCountry');
        Route::get('/state', 'LocationController@getState')->name('getState');
        Route::get('/city', 'LocationController@getCity')->name('getCity');
    });
});

Route::group(['prefix' => 'user', 'namespace' => 'User'], function () {
    Route::post('/registration', 'UserController@registration')->name('user.registration');
    Route::post('/login', 'UserController@doLogin')->name('user.login');
});
