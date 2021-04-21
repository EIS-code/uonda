<?php

namespace App;

use Illuminate\Support\Facades\Validator;
use App\Chat;
use App\User;

class ChatDelete extends BaseModel
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'chat_id', 'user_id'
    ];

    protected $casts = [
        'chat_id' => 'integer',
        'user_id' => 'integer'
    ];

    protected $table = 'chat_delets';

    public function validators(array $data, $returnBoolsOnly = false)
    {
        $validator = Validator::make($data, [
            'chat_id' => ['required', 'exists:' . (new Chat())->getTableName() . ',id'],
            'user_id' => ['required', 'exists:' . (new User())->getTableName() . ',id']
        ]);

        if ($returnBoolsOnly === true) {
            if ($validator->fails()) {
                \Session::flash('error', $validator->errors()->first());
            }

            return !$validator->fails();
        }

        return $validator;
    }
}
