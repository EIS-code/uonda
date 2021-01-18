<?php

namespace App;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use App\User;
use Illuminate\Database\Eloquent\SoftDeletes;

class Notification extends BaseModel
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title', 'message', 'payload', 'device_token', 'is_success', 'apns_id', 'error_infos', 'user_id', 'created_by', 'is_read', 'created_at', 'updated_at'
    ];

    /**
     * The attributes that should be appends to model object.
     *
     * @var array
     */
    public $appends = ['total_notifications', 'total_read_notifications', 'total_unread_notifications'];

    const IS_READ   = '1';
    const IS_UNREAD = '0';
    public $isRead = [
        self::IS_READ   => 'Read',
        self::IS_UNREAD => 'Unread'
    ];

    const IS_SUCCESS = '1';
    const IS_NOT_SUCCESS = '0';
    public $isSuccess = [
        self::IS_SUCCESS => 'Yes',
        self::IS_NOT_SUCCESS => 'No'
    ];

    public function validator(array $data, $returnBoolsOnly = false)
    {
        $validator = Validator::make($data, [
            'message'      => ['required', 'string'],
            'device_token' => ['required', 'string'],
            'user_id'      => ['required', 'integer', 'exists:' . (new User())->getTableName() . ',id'],
            'created_by'   => ['required', 'integer', 'exists:' . (new User())->getTableName() . ',id'],
            'is_success'   => ['nullable', 'in:' . implode(",", array_keys($this->isSuccess))],
            'is_read'      => ['nullable', 'in:' . implode(",", array_keys($this->isRead))]
        ]);

        if ($returnBoolsOnly === true) {
            if ($validator->fails()) {
                \Session::flash('error', $validator->errors()->first());
            }

            return !$validator->fails();
        }

        return $validator;
    }

    public function getIsReadAttribute($value)
    {
        if (empty($value) || !array_key_exists($value, $this->isRead)) {
            return $value;
        }

        return $this->isRead[$value];
    }

    public function getIsSuccessAttribute($value)
    {
        if (empty($value) || !array_key_exists($value, $this->isSuccess)) {
            return $value;
        }

        return $this->isSuccess[$value];
    }

    public function notifications($isAll = false, $isRead = self::IS_UNREAD, $isSuccess = self::IS_SUCCESS)
    {
        if ($isAll) {
            return $this->where('user_id', $this->user_id);
        } else {
            return $this->where('user_id', $this->user_id)->where('is_read', $isRead)->where('is_success', $isSuccess);
        }
    }

    public function getTotalReadNotificationsAttribute()
    {
        return $this->notifications(false, self::IS_READ)->count();
    }

    public function getTotalNotificationsAttribute()
    {
        return $this->notifications(true)->count();
    }

    public function getTotalUnreadNotificationsAttribute()
    {
        return $this->notifications()->count();
    }
}
