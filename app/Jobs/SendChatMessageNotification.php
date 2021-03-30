<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\User;
use App\Feed;
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

    protected $notificationTitle = 'New message received';

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(int $userId, string $message = NULL)
    {
        $this->userId   = $userId;

        $this->message  = $message;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $users = User::where('id', $this->userId)->whereNotNull('device_token')->whereNotNull('device_type')->first();

        if (!empty($users)) {
            // iOs
            if ($users->isIOS()) {
                $options        = [
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

                $deviceToken    = $users->device_token;

                $notification   = new Notification($payload, (string)$deviceToken);

                $client         = new Client($authProvider, $production = false);

                $client->addNotifications([$notification]);

                 // Returns an array of ApnsResponseInterface (one Response per Notification)
                $responses = $client->push();
Log::info($responses);
                return $responses;
            } elseif ($users->isAndroid()) {
                $optionBuilder          = new OptionsBuilder();
                $optionBuilder->setTimeToLive(60 * 20);

                $notificationBuilder    = new PayloadNotificationBuilder($this->notificationTitle);

                $notificationBuilder->setBody($this->message)->setSound('default');

                $option                 = $optionBuilder->build();

                $notification           = $notificationBuilder->build();

                $deviceToken            = $users->device_token;

                $downstreamResponse     = FCM::sendTo($deviceToken, $option, $notification, []);

                $downstreamResponse->numberSuccess();
                $downstreamResponse->numberFailure();
                $downstreamResponse->numberModification();

                // Return Array - you must remove all this tokens in your database
                $downstreamResponse->tokensToDelete();

                // Return Array (key : oldToken, value : new token - you must change the token in your database)
                $downstreamResponse->tokensToModify();

                // Return Array - you should try to resend the message to the tokens in the array
                $downstreamResponse->tokensToRetry();

                // Return Array (key:token, value:error) - in production you should remove from your database the tokens
                $downstreamResponse->tokensWithError();

                return $downstreamResponse;
            }
        }

        return false;
    }
}
