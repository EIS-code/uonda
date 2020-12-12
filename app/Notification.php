<?php

namespace App;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class Notification extends BaseModel
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'message', 'is_read', 'model', 'model_id'
    ];

    const IS_READ   = '1';
    const IS_UNREAD = '0';
    public $isRead = [
        self::IS_READ   => 'Read',
        self::IS_UNREAD => 'Unread'
    ];

    public function validator(array $data, $returnBoolsOnly = false)
    {
        $validator = Validator::make($data, [
            'message'  => ['required', 'string'],
            'is_read'  => ['nullable', 'in:' . implode(",", array_keys($this->isRead))],
            'model'    => ['nullable', 'string', 'max:255'],
            'model_id' => ['required', 'integer']
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
}
