<?php

namespace App;

use App\User;
use Illuminate\Support\Facades\Validator;

class ChatRoomUser extends BaseModel
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'chat_room_id', 'sender_id', 'receiver_id'
    ];

    public function validators(array $data, $returnBoolsOnly = false)
    {
        $validator = Validator::make($data, [
            'chat_room_id'       => ['required', 'integer', 'exists:' . (new ChatRoom())->getTableName() . ',id'],
            'sender_id'          => ['nullable', 'integer', 'exists:' . (new User())->getTableName() . ',id']
        ]);

        if ($returnBoolsOnly === true) {
            if ($validator->fails()) {
                \Session::flash('error', $validator->errors()->first());
            }

            return !$validator->fails();
        }

        return $validator;
    }

    public function validatorUsers(array $data, $returnBoolsOnly = false)
    {
        $validator = Validator::make($data, [
            'sender_id.*' => ['nullable', 'integer', 'exists:' . (new User())->getTableName() . ',id']
        ], [
            'sender_id.*.integer' => _('Please pass proper user id.'),
            'sender_id.*.exists'  => _('Some user id does not exists! Please check.')
        ]);

        if ($returnBoolsOnly === true) {
            if ($validator->fails()) {
                \Session::flash('error', $validator->errors()->first());
            }

            return !$validator->fails();
        }

        return $validator;
    }

    public function Users() {
        return $this->belongsTo(User::class, 'sender_id', 'id');
    }
}
