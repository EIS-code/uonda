<?php

namespace App;

use Illuminate\Support\Facades\Validator;
use App\ChatRoom;
use App\ChatRoomUser;

class Chat extends BaseModel
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'message', 'chat_room_id', 'chat_room_user_id'
    ];

    protected $casts = [
        'message' => 'string',
        'user_id' => 'integer',
        'send_by' => 'integer'
    ];

    public function validators(array $data, $returnBoolsOnly = false)
    {
        $validator = Validator::make($data, [
            'message'           => ['required', 'string', 'max:255'],
            'chat_room_id'      => ['required', 'integer', 'exists:' . (new ChatRoom())->getTableName() . ',id'],
            'chat_room_user_id' => ['required', 'integer', 'exists:' . (new ChatRoomUser())->getTableName() . ',id'],
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
