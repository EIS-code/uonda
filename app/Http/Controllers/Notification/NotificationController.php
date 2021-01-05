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

class NotificationController extends BaseController
{
    public function storeScreenshot(Request $request)
    {
        $data  = $request->all();
        $model = new Notification();

        $modelName = new ReflectionClass((new User));
        $modelName = $modelName->getName();

        $data['message']  = __('Screenshot captured.');
        $data['model']    = $modelName;
        $data['model_id'] = $data['user_id'];

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
            'key_id' => env('PUSH_NOTIFUCTION_IOS_KEY'),
            'team_id' => env('PUSH_NOTIFUCTION_IOS_TEAM_ID'),
            'app_bundle_id' => env('PUSH_NOTIFUCTION_IOS_APP_BUNDLE_ID'),
            'private_key_path' => base_path('iOS/Push Notifications/AuthKey_YZJF23QQMZ.p8'),
            'private_key_secret' => null
        ];

        $authProvider = AuthProvider\Token::create($options);

        $alert = Alert::create()->setTitle('Hello!');
        $alert = $alert->setBody('First push notification');

        $payload = Payload::create()->setAlert($alert);

        // Set notification sound to default
        $payload->setSound('default');

        // Add custom value to your notification, needs to be customized
        $payload->setCustomValue('key', 'value');

        // $deviceTokens = 'fgMpeCBXJkjuiV5dB9Kz7k:APA91bHEOD2POyDAza0KsT5vPYiRA5k45nDna8tAXJT3oz91JSZCfYmdAlVrRLf2bDI9LsqOMDindw8AGPb06rvm_YcI632AbzQmeTkzJQnqGRSzYCChEzdCxyknRCE7yc7KmjFL_ec0';

        $notifications = new PushokNotification($payload, $user->device_token);

        $client = new Client($authProvider, $production = false);
        $client->addNotifications([$notifications]);

        $responses = $client->push();

        foreach ($responses as $response) {
            $res[] = $response->getApnsId();
            $res[] = $response->getStatusCode();
            $res[] = $response->getReasonPhrase();
            $res[] = $response->getErrorReason();
            $res[] = $response->getErrorDescription();
        }

        return $this->returnSuccess(__('Notification sent successfully!'), $res);
    }
}
