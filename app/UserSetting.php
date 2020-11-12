<?php

namespace App;

use Illuminate\Support\Facades\Validator;
use App\User;

class UserSetting extends BaseModel
{
    protected $fillable = [
        'user_name',
        'email',
        'notification',
        'user_id'
    ];

    const CONSTS_PUBLIC  = '1';
    const CONSTS_PRIVATE = '0';

    public $visibilities = [
        self::CONSTS_PUBLIC  => 'Public',
        self::CONSTS_PRIVATE => 'Private'
    ];

    const NOTIFICATION_ON  = '1';
    const NOTIFICATION_OFF = '0';

    public $notifications = [
        self::NOTIFICATION_ON  => 'On',
        self::NOTIFICATION_OFF => 'Off'
    ];

    public function validator(array $data, $id = false, $isUpdate = false)
    {
        return Validator::make($data, [
            'user_name'    => ['required', 'in:' . implode(",", array_keys($this->visibilities))],
            'email'        => ['required', 'in:' . implode(",", array_keys($this->visibilities))],
            'notification' => ['required', 'in:' . implode(",", array_keys($this->notifications))],
            'user_id'      => ['required', 'integer', 'exists:' . (new User())->getTableName() . ',id']
        ]);
    }
}
