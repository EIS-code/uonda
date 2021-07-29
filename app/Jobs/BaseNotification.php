<?php

namespace App\Jobs;

use App\User;
use App\Notification as modalNotification;
use Log;
use LaravelFCM\Message\OptionsBuilder;
use LaravelFCM\Message\PayloadDataBuilder;
use LaravelFCM\Message\PayloadNotificationBuilder;
use FCM;

class BaseNotification
{
    protected $userId;

    protected $requestUserId;

    protected $createdBy;

    protected $isAdmin;

    protected $deviceTokens;

    protected $title;

    protected $description;

    protected $dataPayload;

    protected $downstreamResponse;

    protected $user;

    protected $requestUsers;

    public function __construct(string $title, string $description = NULL, array $dataPayload = [], $isScreenShot = false, $isFeed = false)
    {
        $data                = request()->all();

        $this->userId        = !empty($data['user_id']) ? (int)$data['user_id'] : false;

        $this->requestUserId = !empty($data['request_user_id']) ? (int)$data['request_user_id'] : false;

        $this->createdBy     = $this->userId;

        $this->description   = $description;

        $this->dataPayload   = $dataPayload;

        if (!empty($data['is_admin']) && $data['is_admin'] == '1') {
            $this->isAdmin = User::IS_ADMIN;
        } else {
            $this->isAdmin = User::IS_USER;
        }

        $this->getUser();

        $this->getRequestUsers($isScreenShot, $isFeed);

        if (!empty($this->requestUsers)) {
            if ($isFeed === true) {
                $this->deviceTokens = $this->requestUsers->pluck('device_token', 'id')->toArray();
            } else {
                if ($this->isAdmin == User::IS_ADMIN) {
                    $this->deviceTokens = [User::ADMIN_ID => $this->requestUsers->device_token];
                } else {
                    $this->deviceTokens = [$this->requestUserId => $this->requestUsers->device_token];
                }
            }
        }

        $this->title = $isScreenShot ? $this->user->full_name . ' ' . $title : $title;
    }

    public function getUser()
    {
        $this->user = User::where('id', $this->userId)->first();

        return $this->user;
    }

    public function getRequestUsers($isScreenShot = false, $isFeed = false)
    {
        $requestUser = User::query();

        if ($this->isAdmin == User::IS_USER) {
            $requestUser->whereNotNull('device_token')->whereNotNull('device_type')->has('userPermissionNotificationOn');
        }

        if ($isFeed === true) {
            $requestUser->where('is_admin', User::IS_USER);

            $requestUser = $requestUser->get();
        } else {
            if ($isScreenShot && $this->isAdmin == User::IS_USER) {
                $requestUser->has('userPermissionScreenshotOn');
            }

            if ($this->isAdmin == User::IS_ADMIN) {
                $requestUser->where('id', User::ADMIN_ID)->where('is_admin', $this->isAdmin);
            } else {
                $requestUser->where('id', $this->requestUserId)->where('is_admin', $this->isAdmin);
            }

            $requestUser = $requestUser->first();
        }

        $this->requestUsers = $requestUser;

        return $this->requestUsers;
    }

    public function storeNotification($userId, $token)
    {
        $notification = [
            'title'         => $this->title,
            'payload'       => json_encode($this->dataPayload),
            'message'       => $this->description,
            'device_token'  => $token,
            'is_success'    => modalNotification::IS_SUCCESS,
            'apns_id'       => env('FCM_SERVER_KEY'),
            'error_infos'   => json_encode($this->downstreamResponse->tokensWithError()),
            'user_id'       => $userId,
            'created_by'    => $this->userId
        ];

        $create = modalNotification::create($notification);

        if (!$create) {
            Log::error(json_encode(['Notification store error logs : ' => ['create' => $create, 'notification' => $notification]]));
        }
    }

    protected function send()
    {
        if (empty($this->deviceTokens) || !is_array($this->deviceTokens)) {
            return false;
        }

        if (empty($this->title)) {
            return false;
        }

        if (empty($this->user)) {
            return false;
        }

        if (empty($this->requestUsers)) {
            return false;
        }

        $optionBuilder          = new OptionsBuilder();

        $optionBuilder->setTimeToLive(60 * 20);

        $notificationBuilder    = new PayloadNotificationBuilder($this->title);

        if (!empty($this->description)) {
            $notificationBuilder->setBody($this->description);
        }

        $notificationBuilder->setSound('default');

        $option                 = $optionBuilder->build();

        $notification           = $notificationBuilder->build();

        $data                   = [];

        if (!empty($this->dataPayload)) {
            $dataBuilder = new PayloadDataBuilder();

            $dataBuilder->addData($this->dataPayload);

            $data = $dataBuilder->build();
        }

        $this->downstreamResponse = FCM::sendTo(uniqueValues($this->deviceTokens), $option, $notification, $data);

        foreach ($this->deviceTokens as $userId => $token) {
            $this->storeNotification($userId, $token);
        }
    }
}
