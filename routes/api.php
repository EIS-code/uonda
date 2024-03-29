<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Helper\iOSReceiptHelper;

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

Route::get('/email/verify/{token}','User\UserController@SendEmailVerifyLink')->name('user.email.verify');

Route::group(['middleware' => ['web.auth.api']], function () {

    Route::get('/', 'BaseController@index')->name('index');

    Route::group(['prefix' => 'location', 'namespace' => 'Location'], function () {
        Route::group(['prefix' => 'get'], function () {
            Route::get('/country', 'LocationController@getCountry')->name('getCountry');
            Route::post('/state', 'LocationController@getState')->name('getState');
            Route::post('/city', 'LocationController@getCity')->name('getCity');
            Route::post('/all-cities', 'LocationController@getAllCities')->name('getAllCity');
            Route::post('/cities-with-user-count', 'LocationController@getCitiesWithUserCount')->name('getCityWithUserCount');
            Route::post('/users-based-on-cities', 'LocationController@getUsersBasedOnCity')->name('getUsersBasedOnCity');
            Route::post('/cities/users', 'LocationController@getCitiesBasedOnUsers')->name('getCitiesBasedOnUsers');
        });
    });

    Route::group(['prefix' => 'user', 'namespace' => 'User'], function () {
        Route::post('/details', function() {
            $userId = request()->get('user_id', false);

            return App::make('App\Http\Controllers\User\UserController')->getDetails($userId, true, true);
        })->name('user.get.details');

        Route::get('/delete/{id}', 'UserController@delete')->name('user.delete');

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
            Route::post('/documents', 'UserController@registrationDocuments')->name('user.registration.documents');
            Route::post('/status', 'UserController@getStatus')->name('user.get.status');
        });

        Route::post('/login', 'UserController@doLogin')->name('user.login');

        Route::get('/logout', 'UserController@logoutUser')->name('user.logout');

        Route::get('/get-all-documents', 'UserController@getAllDocuments')->name('user.documents');

        Route::group(['prefix' => 'profile'], function () {
             Route::post('/update', 'UserController@profileUpdate')->name('user.profile.update');

             Route::post('/block', 'UserBlockProfileController@block')->name('user.profile.block');
             Route::post('/unblock', 'UserBlockProfileController@unBlock')->name('user.profile.unblock');
             Route::get('/block/get', 'UserBlockProfileController@getBlock')->name('user.profile.get.block');

            Route::group(['prefix' => 'document'], function () {
                Route::post('/remove', 'UserController@removeDocument')->name('user.profile.unblock');
            });

            Route::group(['prefix' => 'origin'], function () {
                Route::post('/save', 'UserController@saveOriginLocation')->name('user.location.save');
            });
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
            Route::post('/user', 'UserController@getUserExplore');
        });

        Route::post('/location', 'UserController@updateLocation')->name('user.location.update');

        Route::group(['prefix' => 'chat', 'namespace' => 'Chat'], function () {
            Route::post('/send', 'ChatController@sendMessage')->name('user.chat.send');

            Route::get('/list', 'ChatController@getUsersList')->name('user.chat.users.list');

            Route::post('/history', 'ChatController@getUserHistory')->name('user.chat.user.history');

            Route::post('/remove', 'ChatController@removeChat')->name('user.chat.user.remove');

            Route::post('/attachment/send', 'ChatController@sendMessageAttachments')->name('user.chat.attachments.send');

            Route::post('/users/list', 'ChatController@getAllUsersList')->name('user.chat.all.users.list');

            Route::post('/create/group', 'ChatController@createChatGroup')->name('user.chat.create.group');

            Route::post('/add/user/to/group', 'ChatController@addUserToChatGroup')->name('user.chat.add.user');

            Route::post('/remove/user/from/group', 'ChatController@removeUserFromChatGroup')->name('user.chat.remove.user');

            Route::get('/group/details/{id}', 'ChatController@getChatGroupDetails')->name('user.chat.group.details');

            Route::post('/exit/group', 'ChatController@exitChatGroup')->name('user.exit.group');

            Route::post('/report/group', 'ChatController@reportChatGroup')->name('user.report.group');

            Route::post('/delete/group', 'ChatController@deleteChatGroup')->name('user.delete.group');

            Route::post('/list/public/groups', 'ChatController@getPublicGroupLists')->name('user.chat.public.group');

            Route::group(['prefix' => 'notification'], function () {
                Route::post('/message/send', 'ChatController@chatMessage')->name('user.notifications.chat.message.sent');
                Route::post('/message/group/send', 'ChatController@chatMessageGroup')->name('user.notifications.chat.message.sent');
            });

            /* Route::group(['prefix' => 'remove'], function () {
                Route::post('/', 'ChatController@deleteChat')->name('user.chat.remove.chat');
            }); */
        });

        Route::group(['prefix' => 'store'], function () {
            Route::group(['prefix' => 'in-app-purchase'], function () {
                Route::post('/ios', [iOSReceiptHelper::class, 'store'])->name('user.store.iap.ios');
            });
        });
    });

    Route::group(['prefix' => 'constant', 'namespace' => 'Constant'], function () {
        Route::get('/user/terms_and_conditions', 'ConstantController@termsAndConditions')->name('user.constant.terms_and_conditions');
        Route::get('/user/about_us', 'ConstantController@aboutUs')->name('user.constant.about_us');
        Route::get('/user/app/privacy/policy', 'ConstantController@appPrivacyPolicy')->name('user.constant.app_privacy_policy');
    });

    Route::group(['prefix' => 'school', 'namespace' => 'School'], function () {
        Route::any('/get', 'SchoolController@getSchool')->name('getSchool');
        Route::post('/save', 'SchoolController@saveSchool')->name('saveSchool');
        Route::post('/update', 'SchoolController@updateSchool')->name('updateSchool');
    });

    Route::group(['prefix' => 'feed', 'namespace' => 'Feed'], function () {
        Route::get('/get', 'FeedController@getFeed')->name('getFeed');
        Route::post('/get', 'FeedController@getFeedPaginate')->name('get.feed.paginate');
        Route::post('/like', 'FeedController@setFeedLikes')->name('feed-like-dislikes');
    });

    Route::group(['prefix' => 'promotion', 'namespace' => 'Promotion'], function () {
        Route::get('/get', 'PromotionController@getPromotions')->name('getPromotions');
    });

    Route::group(['prefix' => 'notification', 'namespace' => 'Notification'], function () {
        Route::group(['prefix' => 'screenshot'], function () {
            Route::post('/store', 'NotificationController@storeScreenshot')->name('notification.store.screenshot');
        });

        Route::get('/get', 'NotificationController@getNotifications')->name('notifications.get');
        Route::post('/remove', 'NotificationController@removeNotification')->name('notification.remove');
        Route::get('/remove/all', 'NotificationController@removeNotificationAll')->name('notification.remove.all');
        Route::post('/read', 'NotificationController@readNotification')->name('notification.read');

        Route::post('/ios/test', 'NotificationController@testIOS')->name('user.notifications.test.ios');
        Route::post('/android/test', 'NotificationController@testAndroid')->name('user.notifications.test.android');
    });

    Route::group(['prefix' => 'contactus', 'namespace' => 'ContactUs'], function () {
        Route::post('/', 'ContactUsController@store')->name('contactus.store');
    });

});

Route::group(['prefix' => 'test'], function () {
    Route::any('/images', 'BaseController@getImages');
});
