<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\User;
use App\ChatRoom;
use Illuminate\Support\Facades\Validator;

class ReportChatRoom extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'chat_room_id', 'description'
    ];

    public function validator(array $data, $returnBoolsOnly = false)
    {
        $validator = Validator::make($data, [
            'user_id' => ['required', 'integer', 'exists:' . (new User())->getTableName() . ',id'],
            'chat_room_id' => ['required', 'integer', 'exists:' . (new ChatRoom())->getTableName() . ',id'],
            'description' => ['nullable']
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
