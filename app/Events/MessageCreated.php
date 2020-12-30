<?php 

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use App\Chat;

class MessageCreated implements ShouldBroadcast
{
    public $chat;

    public function __construct($chat)
    {
        $this->chat = $chat;
    }

    public function broadcastOn()
    {
        return new Channel('individualJoin-3');
    }

    public function broadcastAs()
    {
        return 'messageRecieve';
    }

    public function broadcastWith()
    {
        $chat = Chat::select(Chat::getTableName() . '.*', Chat::getTableName() . '.id as chat_id')->where(Chat::getTableName() . '.id', $this->chat->id)->first();

        if (!empty($chat)) {
            $user = !empty($chat->sentUser) ? $chat->sentUser : [];

            $chat->user = $user;
        }

        return !empty($chat) ? $chat->toArray() : [];
    }
}
