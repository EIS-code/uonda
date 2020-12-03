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

Route::group(['middleware' => ['auth']], function(){
    Route::get('/', 'DashboardController@index')->name('dashboard');
    Route::resource('users', 'Admin\UserController');
    Route::resource('feeds', 'Admin\FeedsController');
    Route::resource('subscription_plan', 'Admin\SubscriptionController');
    Route::resource('promo-codes', 'Admin\PromoCodeController');
    Route::resource('groups', 'Admin\GroupsController');
    Route::resource('schools', 'Admin\SchoolController');
});


