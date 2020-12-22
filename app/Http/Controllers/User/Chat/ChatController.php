<?php

namespace App\Http\Controllers\User\Chat;

use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use App\Events\MessageCreated;
use App\Chat;
use LRedis;

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
}
