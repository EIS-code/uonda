<?php

namespace App;

use App\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class UserBlockProfile extends BaseModel
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'is_block', 'blocked_by', 'user_id'
    ];

    const IS_NOT_BLOCK = '0';
    const IS_BLOCK     = '1';

    public $isBlock = [
        self::IS_NOT_BLOCK => 'No',
        self::IS_BLOCK     => 'Yes'
    ];

    public function validator(array $data, $returnBoolsOnly = false)
    {
        $validator = Validator::make($data, [
            'is_block'   => ['required', 'in:' . implode(",", array_keys($this->isBlock))],
            'blocked_by' => ['required', 'integer', 'exists:' . (new User())->getTableName() . ',id'],
            'user_ids'   => ['required'],
            'user_ids.*' => ['required', 'integer', 'exists:' . (new User())->getTableName() . ',id']
        ]);

        if ($returnBoolsOnly === true) {
            if ($validator->fails()) {
                \Session::flash('error', $validator->errors()->first());
            }

            return !$validator->fails();
        }

        return $validator;
    }

    public function user()
    {
        return $this->hasOne('App\User', 'id', 'user_id');
    }

    public function blockedUser()
    {
        return $this->hasOne('App\User', 'id', 'blocked_by');
    }
}
