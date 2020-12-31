<?php

namespace App\Http\Controllers\User\Chat;

use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use App\Events\MessageCreated;
use App\Chat;
use App\ChatRoomUser;
use App\ChatRoom;
use App\ChatAttachment;
use App\User;
use LRedis;
use DB;
use Illuminate\Support\Facades\Storage;
// use Illuminate\Broadcasting\BroadcastManager;
// use Illuminate\Support\Facades\Event;
use Illuminate\Http\UploadedFile;

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
        $sendBy = $userId == 3 ? 4 : 3;

        return view('chat', compact('userId', 'sendBy'));
    }

    public function sendMessage(Request $request, BroadcastManager $broadcastManager)
    {
        $model = new Chat();
        $data  = $request->all();

        /*$data['send_by'] = !empty($data['user_id']) ? (int)$data['user_id'] : NULL;
        $data['user_id'] = !empty($data['request_user_id']) ? (int)$data['request_user_id'] : NULL;*/

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
            /*$broadcastManager->event('individualJoin-3', function (Application $app, array $config) {
                dd($app);
            });*/

            // Event::dispatch(new MessageCreated($chat));
            /*$redis = LRedis::connection();

            $redis->publish('messageSend', $chat);*/

            return $this->returnSuccess(__('Message send successfully!'), $chat);
        }

        return $this->returnError(__('Something went wrong!'));
    }

    public function sendMessageAttachments(Request $request)
    {
        $model               = new Chat();
        $modelChatAttachment = new ChatAttachment();
        $modelChatRoomUser   = new ChatRoomUser();
        $modelChatRoom       = new ChatRoom();
        $data                = $request->all();
        $userId              = !empty($data['user_id']) ? (int)$data['user_id'] : false;
        $receiverId          = !empty($data['receiver_id']) ? (int)$data['receiver_id'] : false;
        $isInserted          = false;

        if (empty($userId)) {
            return $this->returnError(__('User id required.'));
        }

        if (empty($receiverId)) {
            return $this->returnError(__('Receiver id required.'));
        }

        if (!empty($data['name']) && empty($data['contacts'])) {
            return $this->returnError(__('Contact required if name present.'));
        } elseif (!empty($data['contacts']) && empty($data['name'])) {
            return $this->returnError(__('Name required if contacts present.'));
        } elseif (empty($data['url']) && empty($data['attachment']) && empty($data['name']) && empty($data['contacts'])) {
            return $this->returnError(__('Provide attachment, url or contacts.'));
        }

        if (!empty($data)) {
            $roomUser = $modelChatRoomUser->where(function($query) use($userId, $receiverId) {
                $query->where(['sender_id' => $userId, 'receiver_id' => $receiverId])
                      ->orWhere(function($query1) use($userId, $receiverId) {
                          $query1->where(['sender_id' => $receiverId, 'receiver_id' => $userId]);
                      });
            })->first();

            if (empty($roomUser)) {
                $chatRoom   = $modelChatRoom->create(['uuid' => $model->generateUuid(10)]);

                $chatRoomId = $chatRoom->id;
            } else {
                $chatRoomId     = $roomUser->chat_room_id;
                $chatRoomUserId = $roomUser->id;
            }

            $chatRoomUser = $modelChatRoomUser->where('sender_id', $userId)->where('receiver_id', $receiverId)->first();

            if (empty($chatRoomUser)) {
                $chatRoomUser = $modelChatRoomUser->create(['chat_room_id' => $chatRoomId, 'sender_id' => $userId, 'receiver_id' => $receiverId]);

                $chatRoomUserId = $chatRoomUser->id;
            }

            // Chat.
            $chat = $model->create(['message' => NULL, 'is_attachment' => $model::IS_ATTACHMENT, 'chat_room_id' => $chatRoomId, 'chat_room_user_id' => $chatRoomUserId]);

            if ($chat) {
                $chatId = $chat->id;

                // Attachments.
                if (!empty($data['attachment']) && $data['attachment'] instanceof UploadedFile) {
                    $attachment = $data['attachment'];
                    $pathInfos  = pathinfo($data['attachment']->getClientOriginalName());
                    $mimeType   = $attachment->getClientMimeType();

                    if (!empty($pathInfos['extension'])) {
                        if ($pathInfos['extension'] == 'm4a') {
                            $mimeType = "audio/mp4";
                        }

                        $fileName  = (empty($pathInfos['filename']) ? time() : $pathInfos['filename']) . '_' . time() . '.' . $pathInfos['extension'];
                        $storeFile = $attachment->storeAs($modelChatAttachment->folder . '\\' . $chatId, $fileName, $modelChatAttachment->fileSystem);

                        if ($storeFile) {
                            $isInserted = $modelChatAttachment->create(['mime_type' => $mimeType, 'attachment' => $fileName, 'chat_id' => $chatId]);

                            if ($isInserted) {
                                return $this->returnSuccess(__('Chat attachment inserted successfully!'), $isInserted);
                            }
                        }
                    }

                    return $this->returnError(__('Chat attachment doesn\'t inserted. Try again.'));

                } elseif (!empty($data['url'])) {
                    $isInserted = $modelChatAttachment->create(['url' => $data['url'], 'chat_id' => $chatId]);

                    if ($isInserted) {
                        return $this->returnSuccess(__('Chat URL inserted successfully!'), $isInserted);
                    }

                    return $this->returnError(__('Chat URL doesn\'t inserted. Try again.'));

                } elseif (!empty($data['name']) && !empty($data['contacts'])) {
                    $isInserted = $modelChatAttachment->create(['name' => $data['name'], 'contacts' => $data['contacts'], 'chat_id' => $chatId]);

                    if ($isInserted) {
                        return $this->returnSuccess(__('Chat contact inserted successfully!'), $isInserted);
                    }

                    return $this->returnError(__('Chat contact doesn\'t inserted. Try again.'));

                }
            }

            if (!$isInserted) {
                $model->find($chatId)->delete();
            }
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

    public function getUserHistory(Request $request)
    {
        $modelChatRoomUsers  = new ChatRoomUser();
        $modelChats          = new Chat();
        $modelChatAttachment = new ChatAttachment();
        $data                = $request->all();

        $userId     = !empty($data['user_id']) ? (int)$data['user_id'] : false;;
        $receiverId = !empty($data['receiver_id']) ? (int)$data['receiver_id'] : false;

        if (empty($userId)) {
            return $this->returnError(__("User id required."));
        }

        if (empty($receiverId)) {
            return $this->returnError(__('Receiver id required.'));
        }

        // , CASE cru.sender_id WHEN '4' THEN 'sender' ELSE 'receiver' END AS sender_receiver_flag

        $records = DB::select("SELECT c.id, c.message, cru.sender_id, cru.receiver_id, c.created_at, c.updated_at, ca.mime_type, ca.attachment, ca.url, ca.name, ca.contacts, CASE WHEN ca.mime_type != '' && ca.attachment != '' THEN 'attachment' WHEN ca.url != '' THEN 'location' WHEN ca.name && ca.contacts THEN 'contacts' ELSE NULL END AS message_type FROM `" . $modelChatRoomUsers::getTableName() . "` AS cru JOIN `" . $modelChats::getTableName() . "` AS c ON cru.id = c.chat_room_user_id LEFT JOIN `" . $modelChatAttachment::getTableName() . "` AS ca ON c.id = ca.chat_id WHERE ((cru.`sender_id` = '" . $userId . "' AND cru.`receiver_id` = '" . $receiverId . "') OR (cru.`sender_id` = '" . $receiverId . "' AND cru.`receiver_id` = '" . $userId . "'))");

        if (!empty($records)) {
            $storageFolderName = (str_ireplace("\\", "/", $modelChatAttachment->folder));

            foreach ($records as &$record) {
                if (!empty($record->created_at) && strtotime($record->created_at) > 0) {
                    $record->created_at = strtotime($record->created_at) * 1000;
                }

                if (!empty($record->updated_at) && strtotime($record->updated_at) > 0) {
                    $record->updated_at = strtotime($record->updated_at) * 1000;
                }

                if (!empty($record->attachment)) {
                    $record->attachment = Storage::disk($modelChatAttachment->fileSystem)->url($storageFolderName . '/' . $record->id . '/' . $record->attachment);;
                }
            }
        }

        return $this->returnSuccess(__('User chat history get successfully!'), $records);
    }
}
