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

Route::group(['middleware' => ['web.auth.api']], function () {

    Route::get('/', 'BaseController@index')->name('index');

    Route::group(['prefix' => 'location', 'namespace' => 'Location'], function () {
        Route::group(['prefix' => 'get'], function () {
            Route::get('/country', 'LocationController@getCountry')->name('getCountry');
            Route::get('/state', 'LocationController@getState')->name('getState');
            Route::get('/city', 'LocationController@getCity')->name('getCity');
        });
    });

    Route::group(['prefix' => 'user', 'namespace' => 'User'], function () {
        Route::post('/details', function() {
            $userId = request()->get('user_id', false);

            return App::make('App\Http\Controllers\User\UserController')->getDetails($userId, true, true);
        })->name('user.get.details');

        Route::post('/details/other', function() {
            $userId = request()->get('request_user_id', false);

            return App::make('App\Http\Controllers\User\UserController')->getDetails($userId, true, true);
        })->name('user.get.details.other');

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

             Route::post('/block', 'UserBlockProfileController@block')->name('user.profile.block');
             Route::post('/unblock', 'UserBlockProfileController@unBlock')->name('user.profile.unblock');
             Route::get('/block/get', 'UserBlockProfileController@getBlock')->name('user.profile.get.block');
        });

        Route::group(['prefix' => 'setting'], function () {
             Route::post('/get/privacy', 'UserSettingController@getPrivacy')->name('user.get.privacy');
             Route::post('/privacy', 'UserSettingController@accountPrivacy')->name('user.setting.privacy');
             Route::post('/notification', 'UserSettingController@userNotification')->name('user.setting.notification');
             Route::post('/screenshot', 'UserSettingController@userScreenshot')->name('user.setting.screenshot');
             Route::post('/change/password', 'UserSettingController@changePassword')->name('user.setting.change.password');
        });

        Route::post('/forgot/password', 'Auth\ForgotPasswordController@sendResetLinkEmail')->name('user.password.email');

        Route::group(['prefix' => 'report', 'namespace' => 'Report'], function () {
            Route::any('/questions/get', 'UserReportQuestionController@getQuestions')->name('user.report.questions');
            Route::post('/', 'UserReportController@postAnswer')->name('user.report.answer');
        });

        Route::group(['prefix' => 'explore'], function () {
            Route::post('/', 'UserController@getExplore')->name('user.explore.get');
        });

        Route::post('/location', 'UserController@updateLocation')->name('user.location.update');

        Route::group(['prefix' => 'chat', 'namespace' => 'Chat'], function () {
            Route::post('/send', 'ChatController@sendMessage')->name('user.chat.send');
        });
    });

    Route::group(['prefix' => 'constant', 'namespace' => 'Constant'], function () {
        Route::get('/user/terms_and_conditions', 'ConstantController@termsAndConditions')->name('user.constant.terms_and_conditions');
        Route::get('/user/about_us', 'ConstantController@aboutUs')->name('user.constant.about_us');
    });

    Route::group(['prefix' => 'school', 'namespace' => 'School'], function () {
        Route::any('/get', 'SchoolController@getSchool')->name('getSchool');
        Route::post('/save', 'SchoolController@saveSchool')->name('saveSchool');
        Route::post('/update', 'SchoolController@updateSchool')->name('updateSchool');
    });

    Route::group(['prefix' => 'feed', 'namespace' => 'Feed'], function () {
        Route::get('/get', 'FeedController@getFeed')->name('getFeed');
    });

    Route::group(['prefix' => 'notification', 'namespace' => 'Notification'], function () {
        Route::group(['prefix' => 'screenshot'], function () {
            Route::post('/store', 'NotificationController@storeScreenshot')->name('notification.store.screenshot');
        });
    });

    Route::group(['prefix' => 'contactus', 'namespace' => 'ContactUs'], function () {
        Route::post('/', 'ContactUsController@store')->name('contactus.store');
    });

});
