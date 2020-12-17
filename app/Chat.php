<?php

namespace App;

use Illuminate\Support\Facades\Validator;
use App\User;

class Chat extends BaseModel
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'message', 'user_id', 'send_by'
    ];

    protected $casts = [
        'message' => 'string',
        'user_id' => 'integer',
        'send_by' => 'integer'
    ];

    public function validators(array $data, $returnBoolsOnly = false)
    {
        $validator = Validator::make($data, [
            'message' => ['nullable', 'string', 'max:255'],
            'user_id' => ['nullable', 'integer', 'exists:' . (new User())->getTableName() . ',id'],
            'send_by' => ['required', 'integer', 'exists:' . (new User())->getTableName() . ',id'],
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

    public function sentUser()
    {
        return $this->hasOne('App\User', 'id', 'send_by');
    }
}
