<?php

use Illuminate\Support\Facades\Route;
use App\User;
use Illuminate\Support\Facades\Auth;

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

Route::post('/store-token', 'Admin\UserController@storeToken')->name('store.token');

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
    Route::get('settings/constants', 'Admin\SettingController@getConstants')->name('settings.constants');
    Route::get('settings/notification/texts', 'Admin\SettingController@getNotificationText')->name('notification.texts');
    Route::post('notification/texts/update', 'Admin\SettingController@updateNotificationText')->name('update.notification.text');
    Route::get('settings/apiResponse/texts', 'Admin\SettingController@getApiResponseText')->name('apiResponse.texts');
    Route::post('apiResponse/texts/update', 'Admin\SettingController@updateApiResponseText')->name('update.apiResponse.text');
    Route::get('settings/email/templates', 'Admin\SettingController@getEmailTemplates')->name('settings.email.templates.get');
    Route::get('settings/email/templates/edit/{id}', 'Admin\SettingController@editEmailTemplate')->name('settings.email.templates.edit');
    Route::put('settings/email/templates/update/{id}', 'Admin\SettingController@updateEmailTemplate')->name('settings.email.templates.update');
    Route::resource('settings', 'Admin\SettingController');
    Route::get('blocked-users', 'Admin\UserController@showBlockedUser')->name('blocked-users');
    Route::resource('emails', 'Admin\EmailController');
    Route::resource('reports-questions', 'Admin\UserReportsQuestionController');
    Route::get('users-reports', 'Admin\UserReportsQuestionController@showUserReports')->name('users-reports');
    Route::resource('promotions', 'Admin\PromotionController');
    Route::get('notification/get-all', 'Admin\NotificationController@getAllNotifications')->name('notification.get.all');
    Route::resource('notification', 'Admin\NotificationController');
    Route::get('notification/{id}/read', 'Admin\NotificationController@read')->name('notification.read');
    Route::post('notification/readAll', 'Admin\NotificationController@readAll')->name('notification.read.all');

    Route::group(['prefix' => 'user', 'namespace' => 'User'], function () {
        Route::group(['prefix' => 'chat', 'namespace' => 'Chat'], function () {
            Route::get('/', 'ChatController@index');
        });
    });

    Route::get('/users-list/{type}', 'Admin\UserController@index')->name('users.index');

    Route::get('/profile', 'Admin\AdminController@editProfile')->name('profile');
    Route::post('/profile-update', 'Admin\AdminController@updateProfile')->name('profile-update');
    
    Route::get('/reset', function() {
        if(auth()->user()->is_admin == User::IS_ADMIN) {
            return Redirect::to('/');
        } else {
            Auth::logout();
            return view('reset_success');
        }
    });

    Route::resource('contactus', 'Admin\ContactUsController');

    Route::get('/import/countries', 'Location\LocationController@importCountries')->name('import.countries');
    Route::get('/import/states', 'Location\LocationController@importStates')->name('import.states');
    Route::get('/import/cities', 'Location\LocationController@importCities')->name('import.cities');
});
