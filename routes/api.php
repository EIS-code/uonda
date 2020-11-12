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
    Route::post('/details', 'UserController@getDetails')->name('user.get.details');

    Route::group(['prefix' => 'registration'], function () {
        // Route::post('/', 'UserController@registration')->name('user.registration');
        Route::post('/personal', 'UserController@registrationPersonal')->name('user.registration.personal');
        Route::post('/school', 'UserController@registrationSchool')->name('user.registration.school');
        Route::post('/other', 'UserController@registrationOther')->name('user.registration.Other');
        Route::post('/document', 'UserController@registrationDocument')->name('user.registration.document');
        Route::post('/status', 'UserController@getStatus')->name('user.get.status');
    });

    Route::post('/login', 'UserController@doLogin')->name('user.login');

    Route::group(['prefix' => 'profile'], function () {
         Route::post('/update', 'UserController@profileUpdate')->name('user.profile.update');
    });
});

Route::group(['prefix' => 'school', 'namespace' => 'School'], function () {
    Route::get('/get', 'SchoolController@getSchool')->name('getSchool');
    Route::post('/save', 'SchoolController@saveSchool')->name('saveSchool');
    Route::post('/update', 'SchoolController@updateSchool')->name('updateSchool');
});
