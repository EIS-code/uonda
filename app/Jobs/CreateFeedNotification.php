<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\User;
use App\Feed;
use App\Notification as modalNotification;
use LaravelFCM\Message\OptionsBuilder;
use LaravelFCM\Message\PayloadDataBuilder;
use LaravelFCM\Message\PayloadNotificationBuilder;
use FCM;
/* use Pushok\AuthProvider;
use Pushok\Client;
use Pushok\Notification;
use Pushok\Payload;
use Pushok\Payload\Alert; */
use Carbon\Carbon;
use Log;

class CreateFeedNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $id;

    protected $title;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($id)
    {
        $this->id = $id;

        $this->title = __('New Feed Created');
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $users = User::where('is_admin', User::IS_USER)->whereNotNull('device_token')->whereNotNull('device_type')->get();

        $feed  = Feed::find($this->id);

        if (empty($users) || $users->isEmpty()) {
            return false;
        }

        if (empty($feed)) {
            return false;
        }

        $deviceTokens = [];

        foreach ($users as $user) {
            if (in_array(strtolower($user->device_type), [User::DEVICE_TYPE_IOS, User::DEVICE_TYPE_ANDROID])) {
                $deviceTokens[$user->id] = $user->device_token;
            }
        }

        if (!empty($deviceTokens)) {
            $optionBuilder = new OptionsBuilder();

            $optionBuilder->setTimeToLive(60 * 20);

            $title       = $this->title;

            $description = $feed->title;

            $notificationBuilder = new PayloadNotificationBuilder($title);

            $notificationBuilder->setBody($description)->setSound('default');

            $dataBuilder = new PayloadDataBuilder();

            $dataBuilder->addData(['notification_type' => modalNotification::NOTIFICATION_FEED]);

            $option             = $optionBuilder->build();
            $notification       = $notificationBuilder->build();
            $data               = $dataBuilder->build();

            $tokens             = $deviceTokens;

            $downstreamResponse = FCM::sendTo($tokens, $option, $notification, $data);

            /* $downstreamResponse->numberSuccess();
            $downstreamResponse->numberFailure();
            $downstreamResponse->numberModification();

            // return Array - you must remove all this tokens in your database
            $downstreamResponse->tokensToDelete();

            // return Array (key : oldToken, value : new token - you must change the token in your database)
            $downstreamResponse->tokensToModify();

            // return Array - you should try to resend the message to the tokens in the array
            $downstreamResponse->tokensToRetry();

            // return Array (key:token, value:error) - in production you should remove from your database the tokens
            $downstreamResponse->tokensWithError(); */

            $notifications = [];

            foreach ($deviceTokens as $userId => $token) {
                $notifications[] = [
                    'title'         => $title,
                    'message'       => $description,
                    'device_token'  => $token,
                    'is_success'    => modalNotification::IS_SUCCESS,
                    'apns_id'       => env('FCM_WEB_API_KEY'),
                    'error_infos'   => json_encode($downstreamResponse->tokensWithError()),
                    'user_id'       => $userId,
                    'created_by'    => User::ADMIN_ID,
                    'created_at'    => Carbon::now(),
                    'updated_at'    => Carbon::now()
                ];
            }

            $create = modalNotification::insert($notifications);

            if (!$create) {
                Log::error(json_encode(['New feed notification logs : ' => $create]));
            }
        }

        /* if (!empty($iosUsers)) {
            $options = [
                'key_id' => env('PUSH_NOTIFICATION_IOS_KEY'), // The Key ID obtained from Apple developer account
                'team_id' => env('PUSH_NOTIFICATION_IOS_TEAM_ID'), // The Team ID obtained from Apple developer account
                'app_bundle_id' => env('PUSH_NOTIFICATION_IOS_APP_BUNDLE_ID'), // The bundle ID for app obtained from Apple developer account
                'private_key_path' => base_path('iOS/Push Notifications/AuthKey_YZJF23QQMZ.p8'), // Path to private key
                'private_key_secret' => null // Private key secret
            ];

            $authProvider = AuthProvider\Token::create($options);

            $alert = Alert::create()->setTitle('New Feed Created');
            $alert = $alert->setBody($feed->title);

            $payload = Payload::create()->setAlert($alert);

            //set notification sound to default
            $payload->setSound('default');

            // add custom value to your notification, needs to be customized
            // $payload->setCustomValue('key', 'value');

            $deviceTokens  = $iosUsers;

            $notifications = [];
            foreach ($deviceTokens as $deviceToken) {
                $notifications[] = new Notification($payload, $deviceToken);
            }

            $client = new Client($authProvider, $production = false);
            $client->addNotifications($notifications);

            $responses = $client->push(); // returns an array of ApnsResponseInterface (one Response per Notification)

            foreach ($responses as $response) {
                $response->getApnsId();
                $response->getStatusCode();
                $response->getReasonPhrase();
                $response->getErrorReason();
                $response->getErrorDescription();
            }
        } */
    }
}
