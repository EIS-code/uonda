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

Auth::routes(['register' => false]);
Route::get('/get-states/{id}', 'Admin\SchoolController@getStateDetails')->name('get-states');
Route::get('/get-cities-of-country/{id}', 'Admin\SchoolController@getCitiesFromCountry')->name('get-cities-of-country');
Route::get('/get-cities/{id}', 'Admin\SchoolController@getCitiesDetails')->name('get-cities');

Route::group(['middleware' => ['auth']], function(){
    Route::get('/', 'DashboardController@index')->name('dashboard');
    Route::resource('users', 'Admin\UserController');
    Route::resource('feeds', 'Admin\FeedsController');
    Route::resource('subscription_plan', 'Admin\SubscriptionController');
    Route::resource('promo-codes', 'Admin\PromoCodeController');
    Route::resource('groups', 'Admin\GroupsController');
    Route::resource('schools', 'Admin\SchoolController');
    Route::resource('chats', 'Admin\ChatController');
    Route::resource('country', 'Admin\CountryController');
    Route::resource('state', 'Admin\StateController');
    Route::resource('city', 'Admin\CityController');
    Route::resource('settings', 'Admin\SettingController');
    Route::get('blocked-users', 'Admin\UserController@showBlockedUser')->name('blocked-users');
    Route::resource('emails', 'Admin\EmailController');
    Route::resource('reports-questions', 'Admin\UserReportsQuestionController');
    Route::get('users-reports', 'Admin\UserReportsQuestionController@showUserReports')->name('users-reports');
    Route::resource('promotions', 'Admin\PromotionController');

    Route::group(['prefix' => 'user', 'namespace' => 'User'], function () {
        Route::group(['prefix' => 'chat', 'namespace' => 'Chat'], function () {
            Route::get('/', 'ChatController@index');
        });
    });

    Route::get('/users-list/{type}', 'Admin\UserController@index')->name('users.index');

    Route::get('/profile', 'Admin\AdminController@editProfile')->name('profile');
    Route::post('/profile-update', 'Admin\AdminController@updateProfile')->name('profile-update');
});


