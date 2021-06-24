<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\User;
use App\Feed;
use App\Notification as NotificationModel;
use LaravelFCM\Message\OptionsBuilder;
use LaravelFCM\Message\PayloadDataBuilder;
use LaravelFCM\Message\PayloadNotificationBuilder;
use FCM;
use Pushok\AuthProvider;
use Pushok\Client;
use Pushok\Notification;
use Pushok\Payload;
use Pushok\Payload\Alert;
use Log;

class SendChatMessageNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $userId;

    protected $message;

    protected $notificationTitle = 'Message received from ';

    protected $fromUserId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(int $userId, string $message = NULL, int $fromUserId = NULL)
    {
        $this->userId               = $userId;

        $this->message              = $message;

        $this->fromUserId           = $fromUserId;

        $fromUser                   = User::find($this->fromUserId);

        $this->notificationTitle    = !empty($fromUser) ? __($this->notificationTitle . $fromUser->fullName) : __($this->notificationTitle);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // ->where('is_online', User::IS_NOT_ONLINE)
        $users = User::where('id', $this->userId)->whereNotNull('device_token')->whereNotNull('device_type')->first();

        if (!empty($users)) {
            // iOs
            if ($users->isIOS()) {
                /*$options        = [
                    'key_id' => env('PUSH_NOTIFICATION_IOS_KEY'), // The Key ID obtained from Apple developer account
                    'team_id' => env('PUSH_NOTIFICATION_IOS_TEAM_ID'), // The Team ID obtained from Apple developer account
                    'app_bundle_id' => env('PUSH_NOTIFICATION_IOS_APP_BUNDLE_ID'), // The bundle ID for app obtained from Apple developer account
                    'private_key_path' => base_path('iOS/Push Notifications/AuthKey_YZJF23QQMZ.p8'), // Path to private key
                    'private_key_secret' => null // Private key secret
                ];

                $authProvider   = AuthProvider\Token::create($options);

                $alert          = Alert::create()->setTitle($this->notificationTitle);

                $alert          = !empty($this->message) ? $alert->setBody($this->message) : $alert;

                $payload        = Payload::create()->setAlert($alert);

                // Set notification sound to default
                $payload->setSound('default');

                $deviceToken    = (string)$users->device_token;

                $notification   = new Notification($payload, $deviceToken);

                $client         = new Client($authProvider, $production = false, [CURLOPT_SSL_VERIFYPEER => env('CURLOPT_SSL_VERIFYPEER')]);

                $client->addNotifications([$notification]);

                 // Returns an array of ApnsResponseInterface (one Response per Notification)
                $responses = $client->push();

                return $responses;*/
            } elseif ($users->isAndroid()) {
                
            }

            $optionBuilder          = new OptionsBuilder();
            $optionBuilder->setTimeToLive(60 * 20);

            $notificationBuilder    = new PayloadNotificationBuilder($this->notificationTitle);

            $dataBuilder->addData(['notification_type' => NotificationModel::NOTIFICATION_CHAT, 'sender_id' => $this->fromUserId]);

            $notificationBuilder->setBody($this->message)->setSound('default');

            $option                 = $optionBuilder->build();

            $notification           = $notificationBuilder->build();

            $deviceToken            = (string)$users->device_token;

            $downstreamResponse     = FCM::sendTo($deviceToken, $option, $notification);

            /* $downstreamResponse->numberSuccess();
            $downstreamResponse->numberFailure();
            $downstreamResponse->numberModification();

            // Return Array - you must remove all this tokens in your database
            $downstreamResponse->tokensToDelete();

            // Return Array (key : oldToken, value : new token - you must change the token in your database)
            $downstreamResponse->tokensToModify();

            // Return Array - you should try to resend the message to the tokens in the array
            $downstreamResponse->tokensToRetry();

            // Return Array (key:token, value:error) - in production you should remove from your database the tokens
            $downstreamResponse->tokensWithError(); */

            $this->storeNotification($deviceToken);

            return $downstreamResponse;
        }

        return false;
    }

    public function storeNotification(string $deviceToken)
    {
        $data = [
            'title'         => $this->notificationTitle,
            'message'       => $this->message,
            'device_token'  => $deviceToken,
            'is_success'    => NotificationModel::IS_SUCCESS,
            'apns_id'       => env('FCM_WEB_API_KEY'),
            'user_id'       => $this->userId,
            'created_by'    => $this->fromUserId,
            'is_read'       => NotificationModel::IS_UNREAD
        ];

        return NotificationModel::create($data);
    }
}
