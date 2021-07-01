<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\User;
use App\Notification as modalNotification;
use LaravelFCM\Message\OptionsBuilder;
use LaravelFCM\Message\PayloadDataBuilder;
use LaravelFCM\Message\PayloadNotificationBuilder;
use FCM;
use Log;

class UserRejectNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $id;

    protected $title;

    protected $dataPayload;

    protected $description;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($id, $dataPayload)
    {
        $this->id           = $id;

        $this->title        = __('You are rejected by Admin.');

        $this->dataPayload  = $dataPayload;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        request()->merge(['show_rejected' => true]);

        $user = User::where('id', $this->id)->where('is_admin', User::IS_USER)->whereNotNull('device_token')->whereNotNull('device_type')->first();

        if (empty($user)) {
            return false;
        }

        $deviceToken   = $user->device_token;

        $optionBuilder = new OptionsBuilder();

        $optionBuilder->setTimeToLive(60 * 20);

        $this->description = $user->reason_for_rejection;

        $title       = $this->title;

        $description = !empty($this->description) ? truncate($this->description, 20) : NULL;

        $notificationBuilder = new PayloadNotificationBuilder($title);

        $notificationBuilder->setBody($description)->setSound('default');

        $dataBuilder         = new PayloadDataBuilder();

        $dataBuilder->addData($this->dataPayload);

        $option             = $optionBuilder->build();
        $notification       = $notificationBuilder->build();
        $data               = $dataBuilder->build();

        $tokens             = $deviceToken;

        $downstreamResponse = FCM::sendTo([$tokens], $option, $notification, $data);

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

        $this->storeNotification($deviceToken, $downstreamResponse);

        request()->merge(['show_rejected' => false]);
    }

    public function storeNotification($token, $downstreamResponse)
    {
        $notification = [
            'title'         => $this->title,
            'payload'       => json_encode($this->dataPayload),
            'message'       => $this->description,
            'device_token'  => $token,
            'is_success'    => modalNotification::IS_SUCCESS,
            'apns_id'       => env('FCM_WEB_API_KEY'),
            'error_infos'   => json_encode($downstreamResponse->tokensWithError()),
            'user_id'       => $this->id,
            'created_by'    => User::ADMIN_ID
        ];

        $create = modalNotification::create($notification);

        if (!$create) {
            Log::error(json_encode(['User rejected notification error logs : ' => $create]));
        }
    }
}
