<?php

namespace App\Http\Controllers\Notification;

use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use App\Notification;
use App\User;
use DB;
use ReflectionClass;
use Pushok\AuthProvider;
use Pushok\Client;
use Pushok\Notification as PushokNotification;
use Pushok\Payload;
use Pushok\Payload\Alert;
use LaravelFCM\Message\OptionsBuilder;
use LaravelFCM\Message\PayloadDataBuilder;
use LaravelFCM\Message\PayloadNotificationBuilder;
use FCM;
use Illuminate\Support\Facades\Storage;

class NotificationController extends BaseController
{
    public function storeScreenshot(Request $request)
    {
        $data          = $request->all();
        $model         = new Notification();
        $userId        = !empty($data['user_id']) ? (int)$data['user_id'] : false;
        $requestUserId = !empty($data['request_user_id']) ? (int)$data['request_user_id'] : false;

        $data['user_id'] = $requestUserId;

        $data['message']    = __('Screenshot captured.');
        $data['created_by'] = $userId;

        if (!empty($data['is_admin']) && $data['is_admin'] == '1') {
            $data['device_token'] = User::ADMIN_DEVICE_TOKEN;
        } elseif (!empty($data['user_id'])) {
            $data['device_token'] = User::getDeviceToken($requestUserId);
        }

        $validator = $model->validator($data);

        if ($validator->fails()) {
            return $this->returnError($validator->errors()->first());
        }

        $create = $model->create($data);

        if ($create) {
            return $this->returnSuccess(__('Notification create successfully!'), $create);
        }

        return $this->returnNull();
    }

    public function testIOS(Request $request)
    {
        /*
            TODO:
            1. Model create
            2. Receiver ID setting.
            3. Success / Errors response set.
        */

        $modelUsers = new User();
        $data       = $request->all();
        $userId     = !empty($data['user_id']) ? (int)$data['user_id'] : false;

        if (empty($userId)) {
            return $this->returnError(__('User id required.'));
        }

        // Check user exists.
        $user = $modelUsers::find($userId);

        $options = [
            'key_id' => env('PUSH_NOTIFICATION_IOS_KEY'),
            'team_id' => env('PUSH_NOTIFICATION_IOS_TEAM_ID'),
            'app_bundle_id' => env('PUSH_NOTIFICATION_IOS_APP_BUNDLE_ID'),
            'private_key_path' => base_path('iOS/Push Notifications/AuthKey_YZJF23QQMZ.p8'),
            'private_key_secret' => null
        ];

        $authProvider = AuthProvider\Token::create($options);

        $alert = Alert::create()->setTitle('Hello!');
        $alert = $alert->setBody('First iOS push notification');

        $payload = Payload::create()->setAlert($alert);

        // Set notification sound to default
        $payload->setSound('default');

        // Add custom value to your notification, needs to be customized
        $payload->setCustomValue('key', 'value');

        // $deviceTokens = 'fgMpeCBXJkjuiV5dB9Kz7k:APA91bHEOD2POyDAza0KsT5vPYiRA5k45nDna8tAXJT3oz91JSZCfYmdAlVrRLf2bDI9LsqOMDindw8AGPb06rvm_YcI632AbzQmeTkzJQnqGRSzYCChEzdCxyknRCE7yc7KmjFL_ec0';

        $notifications = new PushokNotification($payload, (string)$user->device_token);

        $client = new Client($authProvider, $production = false);
        $client->addNotifications([$notifications]);

        $responses = $client->push();

        $res = [];

        foreach ($responses as $response) {
            $res[] = $response->getApnsId();
            $res[] = $response->getStatusCode();
            $res[] = $response->getReasonPhrase();
            $res[] = $response->getErrorReason();
            $res[] = $response->getErrorDescription();
        }

        return $this->returnSuccess(__('Notification sent successfully!'), $res);
    }

    public function testAndroid(Request $request)
    {
        $modelUsers = new User();
        $data       = $request->all();
        $userId     = !empty($data['user_id']) ? (int)$data['user_id'] : false;

        if (empty($userId)) {
            return $this->returnError(__('User id required.'));
        }

        // Check user exists.
        $user = $modelUsers::find($userId);

        $optionBuilder = new OptionsBuilder();
        $optionBuilder->setTimeToLive(60 * 20);

        $notificationBuilder = new PayloadNotificationBuilder('myHello!');
        $notificationBuilder->setBody('First Android push notification')->setSound('default');

        $dataBuilder = new PayloadDataBuilder();
        $dataBuilder->addData(['a_data' => 'my_data']);

        $option = $optionBuilder->build();
        $notification = $notificationBuilder->build();
        $data = $dataBuilder->build();

        $token = $user->device_token;

        $downstreamResponse = FCM::sendTo($token, $option, $notification, $data);

        $downstreamResponse->numberSuccess();
        $downstreamResponse->numberFailure();
        $downstreamResponse->numberModification();

        $downstreamResponse->tokensToDelete();

        $downstreamResponse->tokensToModify();

        $downstreamResponse->tokensToRetry();

        $downstreamResponse->tokensWithError();

        return $this->returnSuccess(__('Notification sent successfully!'));
    }

    public function getNotifications(Request $request)
    {
        $model     = new Notification();
        $modelUser = new User();
        $data      = $request->all();
        $userId    = !empty($data['user_id']) ? (int)$data['user_id'] : false;

        if (empty($userId)) {
            return $this->returnError(__('User id is required.'));
        }

        $notifications = $model::selectRaw($model::getTableName() . '.*, ' . $modelUser->getTableName() . '.profile, ' . $modelUser->getTableName() . '.profile_icon')
                               ->where('user_id', (int)$userId)->where('is_read', $model::IS_UNREAD)->where('is_success', $model::IS_SUCCESS)
                               ->join($modelUser->getTableName(), $model::getTableName() . '.created_by', '=', $modelUser->getTableName() . '.id')->get();

        if (!empty($notifications) && !$notifications->isEmpty($notifications)) {
            $notifications->makeHidden(['total_notifications', 'total_read_notifications', 'total_unread_notifications']);

            $storageFolderNameUser     = (str_ireplace("\\", "/", $modelUser->profile));
            $storageFolderNameUserIcon = (str_ireplace("\\", "/", $modelUser->profileIcon));

            $notifications->map(function($data) use($modelUser, $storageFolderNameUser, $storageFolderNameUserIcon) {
                if (!empty($data->profile)) {
                    $data->profile = Storage::disk($modelUser->fileSystem)->url($storageFolderNameUser . '/' . $data->profile);
                }

                if (!empty($data->profile_icon)) {
                    $data->profile_icon = Storage::disk($modelUser->fileSystem)->url($storageFolderNameUserIcon . '/' . $data->profile_icon);
                }

                if (!empty($data->updated_at) && strtotime($data->updated_at) > 0) {
                    $data->time = strtotime($data->updated_at) * 1000;
                } else {
                    $data->time = 0;
                }
            });
        }

        return $this->returnSuccess(__('Notifications get successfully!'), $notifications);
    }

    public function removeNotification(Request $request)
    {
        $model  = new Notification();
        $data   = $request->all();
        $userId = !empty($data['user_id']) ? (int)$data['user_id'] : false;
        $id     = !empty($data['id']) ? (int)$data['id'] : false;

        if (empty($userId)) {
            return $this->returnError(__('User id is required.'));
        }

        if (empty($id)) {
            return $this->returnError(__('Notification id is required.'));
        }

        $notification = $model::where('user_id', (int)$userId)->where('id', (int)$id)->first();
        $userId       = !empty($notification) ? $notification->user_id : false;

        if (!empty($userId)) {
            $remove = $notification->delete();

            if ($remove) {
                return $this->returnSuccess(__('Notification removed successfully!'), $this->getDetails(0, $userId, true));
            }
        }

        return $this->returnError(__('Notification could\'t found.'));
    }

    public function readNotification(Request $request)
    {
        $model  = new Notification();
        $data   = $request->all();
        $userId = !empty($data['user_id']) ? (int)$data['user_id'] : false;
        $id     = !empty($data['id']) ? (int)$data['id'] : false;

        if (empty($userId)) {
            return $this->returnError(__('User id is required.'));
        }

        if (empty($id)) {
            return $this->returnError(__('Notification id is required.'));
        }

        $notification = $model::where('user_id', (int)$userId)->where('id', (int)$id);
        $isRead       = $notification->update(['is_read' => $model::IS_READ]);

        if ($isRead) {
            $notification = $notification->first();

            return $this->returnSuccess(__('Notification read successfully!'), $this->getDetails($notification->id, false, true));
        }

        return $this->returnError(__('Notification could\'t found.'));
    }

    public function getDetails(int $id, $userId = false, $totalOnly = false, $isApi = false)
    {
        $model = new Notification();

        if (empty($id) && !empty($userId)) {
            $notification = $model::where('user_id', $userId)->first();
        } else {
            $notification = $model::find($id);
        }

        if (!empty($notification)) {
            if ($isApi) {
                if ($totalOnly) {
                    return $this->returnSuccess(__('Notification details get successfully!'), ['total_notifications' => $notification->total_notifications, 'total_read_notifications' => $notification->total_read_notifications, 'total_unread_notifications' => $notification->total_unread_notifications]);
                }

                return $this->returnSuccess(__('Notification details get successfully!'), $notification);
            }

            if ($totalOnly) {
                return ['total_notifications' => $notification->total_notifications, 'total_read_notifications' => $notification->total_read_notifications, 'total_unread_notifications' => $notification->total_unread_notifications];
            }

            return $notification;
        }

        if ($isApi) {
            return $this->returnNull();
        }

        return $notification;
    }
}
