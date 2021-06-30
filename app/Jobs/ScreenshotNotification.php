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
use Carbon\Carbon;
use Illuminate\Http\Request;
use Log;

class ScreenshotNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $userId;

    protected $requestUserId;

    protected $createdBy;

    protected $isAdmin;

    protected $deviceToken;

    protected $title;

    protected $description;

    protected $dataPayload;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Request $request)
    {
        $data                = $request->all();

        $this->userId        = !empty($data['user_id']) ? (int)$data['user_id'] : false;

        $this->requestUserId = !empty($data['request_user_id']) ? (int)$data['request_user_id'] : false;

        $this->createdBy     = $this->userId;

        $this->title         = __('Takes a screen shot.');

        $this->dataPayload   = ['notification_type' => modalNotification::NOTIFICATION_SCREENSHOT_CAPTURED];

        if (!empty($data['is_admin']) && $data['is_admin'] == '1') {
            $this->deviceToken = User::ADMIN_DEVICE_TOKEN;

            $this->isAdmin     = User::IS_ADMIN;
        } else {
            $this->deviceToken = User::getDeviceToken($this->requestUserId);

            $this->isAdmin     = User::IS_USER;
        }
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if (empty($this->deviceToken)) {
            return false;
        }

        $user = User::where('id', $this->userId)->where('is_admin', $this->isAdmin)->whereNotNull('device_token')->whereNotNull('device_type')->first();

        if (empty($user)) {
            return false;
        }

        $optionBuilder = new OptionsBuilder();

        $optionBuilder->setTimeToLive(60 * 20);

        $title       = $user->full_name . ' ' . $this->title;

        $this->title = $title;

        $notificationBuilder = new PayloadNotificationBuilder($title);

        $notificationBuilder->setBody($this->description)->setSound('default');

        $dataBuilder         = new PayloadDataBuilder();

        $dataBuilder->addData($this->dataPayload);

        $option             = $optionBuilder->build();
        $notification       = $notificationBuilder->build();
        $data               = $dataBuilder->build();

        $token              = $this->deviceToken;

        $downstreamResponse = FCM::sendTo([$token], $option, $notification, $data);

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

        $this->storeNotification($this->deviceToken, $downstreamResponse);
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
            'user_id'       => $this->requestUserId,
            'created_by'    => $this->userId
        ];

        $create = modalNotification::create($notification);

        if (!$create) {
            Log::error(json_encode(['Screenshot notification error logs : ' => $create]));
        }
    }
}
