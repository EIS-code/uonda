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

Auth::routes(['register' => false, 'reset' => false]);
Route::get('/get-states/{id}', 'Admin\SchoolController@getStateDetails')->name('get-states');
Route::get('/get-cities/{id}', 'Admin\SchoolController@getCitiesDetails')->name('get-cities');

Route::group(['middleware' => ['auth']], function(){
    Route::get('/', 'DashboardController@index')->name('dashboard');
    Route::resource('users', 'Admin\UserController');
    Route::resource('feeds', 'Admin\FeedsController');
    Route::resource('subscription_plan', 'Admin\SubscriptionController');
    Route::resource('promo-codes', 'Admin\PromoCodeController');
    Route::resource('groups', 'Admin\GroupsController');
    Route::resource('schools', 'Admin\SchoolController');
    Route::get('/profile', 'Admin\AdminController@editProfile')->name('profile');
    Route::post('/profile-update', 'Admin\AdminController@updateProfile')->name('profile-update');
});


