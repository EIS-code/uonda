<?php

namespace App\Http\Controllers\User\Chat;

use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use App\Events\MessageCreated;
use App\Chat;
use App\ChatRoomUser;
use App\User;
use LRedis;
use DB;
use Illuminate\Support\Facades\Storage;

class ChatController extends BaseController
{
    /*public function __construct()
    {
        // $this->middleware('guest');
        parent::__construct();
    }*/

    public function index()
    {
        $user = auth()->user();

        $userId = auth()->user()->id;
        $sendBy = $userId == 2 ? 3 : 2;

        return view('chat', compact('userId', 'sendBy'));
    }

    public function sendMessage(Request $request)
    {
        $model = new Chat();
        $data  = $request->all();

        $data['send_by'] = !empty($data['user_id']) ? (int)$data['user_id'] : NULL;
        $data['user_id'] = !empty($data['request_user_id']) ? (int)$data['request_user_id'] : NULL;

        /*if (env('APP_ENV') == 'local' || env('APP_ENV') == 'dev') {
            $userId = auth()->user()->id;
            $sendBy = $userId == 2 ? 3 : 2;

            $data['user_id'] = $userId;
            $data['send_by'] = $sendBy;
        }*/

        $validator = $model->validators($data);

        if ($validator->fails()) {
            return $this->returnError($validator->errors()->first());
        }

        $chat = $model::create($data);

        if ($chat) {
            // broadcast(new MessageCreated($chat));
            /*$redis = LRedis::connection();

            $redis->publish('messageSend', $chat);*/

            return $this->returnSuccess(__('Message send successfully!'), $chat);
        }

        return $this->returnError(__('Something went wrong!'));
    }

    public function getUsersList(Request $request)
    {
        $model              = new User();
        $modelChatRoomUsers = new ChatRoomUser();
        $modelChats         = new Chat();

        $userId = $request->get('user_id', false);

        if (empty($userId)) {
            return $this->returnError(__("User id can not be empty."));
        }

        $storageFolderName = (str_ireplace("\\", "/", $model->profile));

        $records = DB::select("SELECT u.id, u.profile, u.name, c.updated_at AS recent_time, c.message AS recent_message from `" . $model->getTableName() . "` AS u
                    JOIN `" . $modelChatRoomUsers::getTableName() . "` AS crm ON u.id = crm.sender_id OR u.id = crm.receiver_id
                    LEFT JOIN `" . $modelChats::getTableName() . "` AS c ON crm.id = c.chat_room_user_id AND c.updated_at = (SELECT (MAX(c2.updated_at)) FROM `" . $modelChats::getTableName() . "` AS c2 WHERE crm.id = c2.chat_room_user_id LIMIT 1)
                    WHERE u.id != '" . $userId . "'
                    GROUP BY u.id
                    ORDER BY c.updated_at DESC
            ");

        if (!empty($records)) {
            foreach ($records as &$record) {
                if (empty($record->profile)) {
                    continue;
                }

                $record->profile = Storage::disk($model->fileSystem)->url($storageFolderName . '/' . $record->profile);
            }
        }

        return $this->returnSuccess(__('User chat list get successfully!'), $records);
    }
}
