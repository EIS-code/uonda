<?php

namespace App;

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
}
