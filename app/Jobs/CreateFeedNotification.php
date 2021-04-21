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

class CreateFeedNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $id;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($id)
    {
        $this->id = $id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $users = User::where('is_admin', 0)
                    ->whereNotNull('device_token')
                    ->whereNotNull('device_type')
                    ->get();
        $feed = Feed::find($this->id);
        $android_users = array();
        $ios_users = array();
        foreach($users as $user) {
            if($user->device_type == 'android') {
                $android_users[] = $user->device_token;
            } else {
                $ios_users[] = $user->device_token;
            }
        }
        if(!empty($android_users)) {
            Log::info('Android');
            $optionBuilder = new OptionsBuilder();
            $optionBuilder->setTimeToLive(60*20);

            $notificationBuilder = new PayloadNotificationBuilder('New Feed Created');
            $notificationBuilder->setBody($feed->title)
                                ->setSound('default');

            $dataBuilder = new PayloadDataBuilder();
            $dataBuilder->addData(['a_data' => 'my_data']);

            $option = $optionBuilder->build();
            $notification = $notificationBuilder->build();
            $data = $dataBuilder->build();

            $tokens = $android_users;
            Log::info($tokens);
            $downstreamResponse = FCM::sendTo($tokens, $option, $notification, $data);

            $downstreamResponse->numberSuccess();
            $downstreamResponse->numberFailure();
            $downstreamResponse->numberModification();

            // return Array - you must remove all this tokens in your database
            $downstreamResponse->tokensToDelete();

            // return Array (key : oldToken, value : new token - you must change the token in your database)
            $downstreamResponse->tokensToModify();

            // return Array - you should try to resend the message to the tokens in the array
            $downstreamResponse->tokensToRetry();

            // return Array (key:token, value:error) - in production you should remove from your database the tokens
            $downstreamResponse->tokensWithError();
            Log::info($downstreamResponse->numberSuccess());
        } 
        if (!empty($ios_users)) {
            Log::info('Ios');
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
            
            //add custom value to your notification, needs to be customized
            $payload->setCustomValue('key', 'value');
            
            $deviceTokens = $ios_users;
            Log::info($deviceTokens);
            $notifications = [];
            foreach ($deviceTokens as $deviceToken) {
                $notifications[] = new Notification($payload,$deviceToken);
            }
            $client = new Client($authProvider, $production = false);
            $client->addNotifications($notifications);
            
            $responses = $client->push(); // returns an array of ApnsResponseInterface (one Response per Notification)
            Log::info($responses);
            foreach ($responses as $response) {
                $response->getApnsId();
                $response->getStatusCode();
                $response->getReasonPhrase();
                $response->getErrorReason();
                $response->getErrorDescription();
            }
        }
    }
}
