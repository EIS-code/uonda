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
        $user    = auth()->user();
        $request = request();

        $userId = auth()->user()->id;
        // $sendBy = $userId == 3 ? 4 : 3;
        $sendBy = $request->get('receiver_id', ($userId == 3 ? 4 : 3));

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
        $groupId             = !empty($data['group_id']) ? (int)$data['group_id'] : false;
        $isInserted          = false;

        if (empty($userId)) {
            return $this->returnError(__('User id required.'));
        }

        if (empty($receiverId) && empty($groupId)) {
            return $this->returnError(__('Receiver id or Group id required.'));
        }

        if (!empty($data['name']) && empty($data['contacts'])) {
            return $this->returnError(__('Contact required if name present.'));
        } elseif (!empty($data['contacts']) && empty($data['name'])) {
            return $this->returnError(__('Name required if contacts present.'));
        }/* elseif (!empty($data['url']) && empty($data['address'])) {
            return $this->returnError(__('Address required if URL present.'));
        } elseif (empty($data['url']) && !empty($data['address'])) {
            return $this->returnError(__('URL required if Address present.'));
        } */elseif (empty($data['url']) && empty($data['attachment']) && empty($data['name']) && empty($data['contacts'])) {
            return $this->returnError(__('Provide attachment, url or contacts.'));
        }

        if (!empty($data)) {
            $group = "";

            if (!empty($receiverId)) {
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
                } else {
                    $chatRoomUserId = $chatRoomUser->id;
                }
            } else {
                $chatRoom = $modelChatRoom::find($groupId);

                if (empty($chatRoom)) {
                    return $this->returnError(__('Group not found.'));
                }

                $chatRoomUser = $modelChatRoomUser::where('chat_room_id', (int)$groupId)->where('sender_id', (int)$userId)->first();

                if (empty($chatRoomUser)) {
                    return $this->returnError(__('User not added in this group.'));
                }

                $chatRoomUserId = $chatRoomUser->id;
                $chatRoomId     = $groupId;

                $group = __('group');
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
                        $fileName  = removeSpaces($fileName);
                        $storeFile = $attachment->storeAs($modelChatAttachment->folder . '\\' . $chatId, $fileName, $modelChatAttachment->fileSystem);

                        if ($storeFile) {
                            $isInserted = $modelChatAttachment->create(['mime_type' => $mimeType, 'attachment' => $fileName, 'chat_id' => $chatId]);

                            if ($isInserted) {
                                return $this->returnSuccess(__('Chat ' . $group . ' attachment inserted successfully!'), $isInserted);
                            }
                        }
                    }

                    return $this->returnError(__('Chat ' . $group . ' attachment doesn\'t inserted. Try again.'));

                } elseif (!empty($data['url'])) {
                    $address = !empty($data['address']) ? $data['address'] : NULL;

                    $isInserted = $modelChatAttachment->create(['url' => $data['url'], 'address' => $address, 'chat_id' => $chatId]);

                    if ($isInserted) {
                        return $this->returnSuccess(__('Chat ' . $group . ' URL & Address inserted successfully!'), $isInserted);
                    }

                    return $this->returnError(__('Chat ' . $group . ' URL & Address doesn\'t inserted. Try again.'));

                } elseif (!empty($data['name']) && !empty($data['contacts'])) {
                    $isInserted = $modelChatAttachment->create(['name' => $data['name'], 'contacts' => $data['contacts'], 'chat_id' => $chatId]);

                    if ($isInserted) {
                        return $this->returnSuccess(__('Chat ' . $group . ' contact inserted successfully!'), $isInserted);
                    }

                    return $this->returnError(__('Chat ' . $group . ' contact doesn\'t inserted. Try again.'));

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
        $model                = new User();
        $modelChatRoomUsers   = new ChatRoomUser();
        $modelChats           = new Chat();
        $modelChatRooms       = new ChatRoom();
        $modelChatAttachments = new ChatAttachment();

        $userId = $request->get('user_id', false);

        if (empty($userId)) {
            return $this->returnError(__("User id can not be empty."));
        }

        $storageFolderName     = (str_ireplace("\\", "/", $model->profile));
        $storageFolderNameIcon = (str_ireplace("\\", "/", $model->profileIcon));

        /*$records = DB::select("SELECT u.id, u.profile, u.name, c.updated_at AS recent_time, c.message AS recent_message from `" . $model->getTableName() . "` AS u
                    JOIN `" . $modelChatRoomUsers::getTableName() . "` AS crm ON u.id = crm.sender_id OR u.id = crm.receiver_id
                    LEFT JOIN `" . $modelChats::getTableName() . "` AS c ON crm.id = c.chat_room_user_id AND c.updated_at = (SELECT (MAX(c2.updated_at)) FROM `" . $modelChats::getTableName() . "` AS c2 WHERE crm.id = c2.chat_room_user_id LIMIT 1)
                    WHERE u.id != '" . $userId . "'
                    GROUP BY u.id
                    ORDER BY c.updated_at DESC
            ");*/

        $records = $modelChats::selectRaw("{$modelChats::getTableName()}.id as chat_id, {$modelChatRoomUsers::getTableName()}.sender_id, {$modelChatRoomUsers::getTableName()}.receiver_id, {$modelChatRooms::getTableName()}.id as chat_room_id, {$modelChats::getTableName()}.updated_at, CASE WHEN {$modelChatAttachments::getTableName()}.mime_type != '' && {$modelChatAttachments::getTableName()}.attachment != '' THEN 'Attachment Sent' WHEN {$modelChatAttachments::getTableName()}.url != '' THEN 'URL Sent' WHEN {$modelChatAttachments::getTableName()}.name != '' && {$modelChatAttachments::getTableName()}.contacts != '' THEN 'Contact Sent' ELSE {$modelChats::getTableName()}.message END AS recent_message, {$modelChatRooms::getTableName()}.is_group, {$modelChatRooms::getTableName()}.title")
                              ->join($modelChatRoomUsers::getTableName(), $modelChats::getTableName() . '.chat_room_user_id', '=', $modelChatRoomUsers::getTableName() . '.id')
                              ->join($modelChatRooms::getTableName(), $modelChatRoomUsers::getTableName() . '.chat_room_id', '=', $modelChatRooms::getTableName() . '.id')
                              ->leftJoin($modelChatAttachments::getTableName(), $modelChats::getTableName() . '.id', '=', $modelChatAttachments::getTableName() . '.chat_id')
                              ->whereRaw($modelChatRoomUsers::getTableName() . '.receiver_id = ' . $userId . ' OR ' . $modelChatRoomUsers::getTableName() . '.sender_id = ' . $userId)
                              ->where($modelChatRooms::getTableName() . '.is_group', $modelChatRooms::IS_NOT_GROUP)
                              ->orderBy($modelChats::getTableName() . '.updated_at', 'ASC')
                              ->get();

        $returnDatas = [];

        if (!empty($records)) {
            $userIds = $records->pluck('sender_id');
            $userIds = $userIds->merge($records->pluck('receiver_id'));
            $users   = $model::selectRaw('*, profile as profile_image, profile_icon as profile_image_icon')->whereIn('id', $userIds->unique())->get()->keyBy('id');

            $records->map(function($data) use($users, $userId, $storageFolderName, $storageFolderNameIcon, &$returnDatas) {
                $user = false;

                if ($data->sender_id == $userId) {
                    if (!empty($users[$data->receiver_id])) {
                        $opponentId = $data->receiver_id;
                        $user       = $users[$data->receiver_id];
                    }
                } elseif (!empty($users[$data->sender_id])) {
                    $opponentId = $data->sender_id;
                    $user       = $users[$data->sender_id];
                }

                if (!empty($user)) {
                    $returnDatas[$user->id] = [
                        /*'sender_id'         => $data->sender_id,
                        'receiver_id'       => $data->receiver_id,*/
                        'user_id'           => $opponentId,
                        'chat_id'           => $data->chat_id,
                        'chat_room_id'      => $data->chat_room_id,
                        'name'              => $user->name,
                        'profile'           => !empty($user->profile_image) ? Storage::disk($user->fileSystem)->url($storageFolderName . '/' . $user->profile_image) : NULL,
                        'profile_icon'      => !empty($user->profile_image_icon) ? Storage::disk($user->fileSystem)->url($storageFolderNameIcon . '/' . $user->profile_image_icon) : NULL,
                        'recent_time'       => strtotime($data->updated_at) * 1000,
                        'recent_message'    => $data->recent_message,
                        'is_group'          => $data->is_group,
                        'is_online'         => $user->is_online,
                        'socket_id'         => $user->socket_id,
                        'title'             => $data->title
                    ];
                }
            });
        }

        $records = $modelChatRooms::selectRaw("{$modelChats::getTableName()}.id as chat_id, {$modelChatRooms::getTableName()}.id, {$modelChatRooms::getTableName()}.id as chat_room_id, {$modelChats::getTableName()}.updated_at, CASE WHEN {$modelChatAttachments::getTableName()}.mime_type != '' && {$modelChatAttachments::getTableName()}.attachment != '' THEN 'Attachment Sent' WHEN {$modelChatAttachments::getTableName()}.url != '' THEN 'URL Sent' WHEN {$modelChatAttachments::getTableName()}.name != '' && {$modelChatAttachments::getTableName()}.contacts != '' THEN 'Contact Sent' ELSE {$modelChats::getTableName()}.message END AS recent_message, {$modelChatRooms::getTableName()}.is_group, {$modelChatRooms::getTableName()}.title, {$modelChatRooms::getTableName()}.group_icon, {$modelChatRooms::getTableName()}.group_icon_actual")
                              ->join($modelChatRoomUsers::getTableName(), $modelChatRooms::getTableName() . '.id', '=', $modelChatRoomUsers::getTableName() . '.chat_room_id')
                              ->leftJoin($modelChats::getTableName(), $modelChatRooms::getTableName() . '.id', '=', $modelChats::getTableName() . '.chat_room_id')
                              ->leftJoin($modelChatAttachments::getTableName(), $modelChats::getTableName() . '.id', '=', $modelChatAttachments::getTableName() . '.chat_id')
                              ->where($modelChatRooms::getTableName() . '.is_group', $modelChatRooms::IS_GROUP)
                              ->where($modelChatRoomUsers::getTableName() . '.sender_id', $userId)
                              ->orderBy($modelChats::getTableName() . '.updated_at', 'ASC')
                              ->get();

        $returnGroupDatas = [];

        if (!empty($records)) {
            $records->map(function($data) use(&$returnGroupDatas, $modelChatRooms) {
                $returnGroupDatas[$data->chat_room_id] = [
                    'chat_id'            => $data->chat_id,
                    'chat_room_id'       => $data->chat_room_id,
                    'recent_time'        => strtotime($data->updated_at) * 1000,
                    'recent_message'     => $data->recent_message,
                    'is_group'           => $data->is_group,
                    'title'              => $data->title,
                    'group_icon'         => $data->group_icon,
                    'group_icon_actual'  => $data->group_icon_actual,
                    'total_participants' => $modelChatRooms->totalGroupParticipants($data->chat_room_id)
                ];
            });
        }

        $return = array_merge($returnDatas, $returnGroupDatas);

        // Sortings.
        if (!empty($return)) {
            usort($return, function($a, $b) {
                $t1 = $a['recent_time'];
                $t2 = $b['recent_time'];

                return $t2 - $t1;
            });
        }

        return $this->returnSuccess(__('User chat list get successfully!'), $return);
    }

    public function getUserHistory(Request $request)
    {
        $modelChatRoomUsers  = new ChatRoomUser();
        $modelChats          = new Chat();
        $modelChatAttachment = new ChatAttachment();
        $modelChatRooms      = new ChatRoom();
        $model               = new User();
        $data                = $request->all();

        $userId     = !empty($data['user_id']) ? (int)$data['user_id'] : false;;
        $receiverId = !empty($data['receiver_id']) ? (int)$data['receiver_id'] : false;
        $groupId    = !empty($data['group_id']) ? (int)$data['group_id'] : false;

        if (empty($userId)) {
            return $this->returnError(__("User id is required."));
        }

        /*if (empty($groupId)) {
            return $this->returnError(__('Group id is required.'));
        }*/

        if (empty($receiverId) && empty($groupId)) {
            return $this->returnError(__('Receiver id or Group id required.'));
        }

        // , CASE cru.sender_id WHEN '4' THEN 'sender' ELSE 'receiver' END AS sender_receiver_flag

        /*if (!empty($receiverId)) {
            $records = DB::select("SELECT c.id, c.message, cru.sender_id, cru.receiver_id, c.created_at, c.updated_at, ca.mime_type, ca.attachment, ca.url, ca.name, ca.contacts, CASE WHEN ca.mime_type != '' && ca.attachment != '' THEN 'attachment' WHEN ca.url != '' THEN 'location' WHEN ca.name && ca.contacts THEN 'contacts' ELSE NULL END AS message_type FROM `" . $modelChatRoomUsers::getTableName() . "` AS cru JOIN `" . $modelChats::getTableName() . "` AS c ON cru.id = c.chat_room_user_id JOIN `" . $modelChatRooms::getTableName() . "` AS cr ON cru.chat_room_id = cr.id LEFT JOIN `" . $modelChatAttachment::getTableName() . "` AS ca ON c.id = ca.chat_id WHERE ((cru.`sender_id` = '" . $userId . "' AND cru.`receiver_id` = '" . $receiverId . "') OR (cru.`sender_id` = '" . $receiverId . "' AND cru.`receiver_id` = '" . $userId . "')) AND cr.is_group = '" . $modelChatRooms::IS_NOT_GROUP . "'
                ORDER BY c.updated_at ASC");
        } else {
            $records = DB::select("SELECT c.id, c.message, cru.sender_id, cru.receiver_id, c.created_at, c.updated_at, ca.mime_type, ca.attachment, ca.url, ca.name, ca.contacts, CASE WHEN ca.mime_type != '' && ca.attachment != '' THEN 'attachment' WHEN ca.url != '' THEN 'location' WHEN ca.name && ca.contacts THEN 'contacts' WHEN c.message != '' THEN 'text' ELSE NULL END AS message_type, u.name as user_name, u.profile
                FROM `" . $modelChatRoomUsers::getTableName() . "` AS cru
                JOIN `" . $modelChats::getTableName() . "` AS c ON cru.id = c.chat_room_user_id
                JOIN `" . $modelChatRooms::getTableName() . "` AS cr ON cru.chat_room_id = cr.id
                JOIN `" . $model->getTableName() . "` AS u ON cru.sender_id = u.id
                LEFT JOIN `" . $modelChatAttachment::getTableName() . "` AS ca ON c.id = ca.chat_id
                WHERE cr.is_group = '" . $modelChatRooms::IS_GROUP . "' AND cr.id = '" . (int)$groupId . "'
                ORDER BY c.updated_at ASC");
        }*/

        if (!empty($receiverId)) {
            $records = DB::select("SELECT c.id, c.message, cru.sender_id, cru.receiver_id, c.created_at, c.updated_at, ca.mime_type, ca.attachment, ca.url, ca.name, ca.contacts, CASE WHEN ca.mime_type != '' && ca.attachment != '' THEN 'attachment' WHEN ca.url != '' THEN 'location' WHEN ca.name && ca.contacts THEN 'contacts' WHEN c.message != '' THEN 'text' ELSE NULL END AS message_type, u.name as user_name, u.profile, u.profile_icon
                    FROM `" . $modelChatRoomUsers::getTableName() . "` AS cru
                    JOIN `" . $modelChats::getTableName() . "` AS c ON cru.id = c.chat_room_user_id
                    JOIN `" . $modelChatRooms::getTableName() . "` AS cr ON cru.chat_room_id = cr.id
                    JOIN `" . $model->getTableName() . "` AS u ON cru.sender_id = u.id
                    LEFT JOIN `" . $modelChatAttachment::getTableName() . "` AS ca ON c.id = ca.chat_id
                    WHERE ((cru.`sender_id` = '" . $userId . "' AND cru.`receiver_id` = '" . $receiverId . "') OR (cru.`sender_id` = '" . $receiverId . "' AND cru.`receiver_id` = '" . $userId . "')) AND cr.is_group = '" . $modelChatRooms::IS_NOT_GROUP . "'
                    ORDER BY c.updated_at ASC");
        } else {
            $records = DB::select("SELECT c.id, c.message, cru.sender_id, cru.receiver_id, c.created_at, c.updated_at, ca.mime_type, ca.attachment, ca.url, ca.name, ca.contacts, CASE WHEN ca.mime_type != '' && ca.attachment != '' THEN 'attachment' WHEN ca.url != '' THEN 'location' WHEN ca.name && ca.contacts THEN 'contacts' WHEN c.message != '' THEN 'text' ELSE NULL END AS message_type, u.name as user_name, u.profile, u.profile_icon
                    FROM `" . $modelChatRoomUsers::getTableName() . "` AS cru
                    JOIN `" . $modelChats::getTableName() . "` AS c ON cru.id = c.chat_room_user_id
                    JOIN `" . $modelChatRooms::getTableName() . "` AS cr ON cru.chat_room_id = cr.id
                    JOIN `" . $model->getTableName() . "` AS u ON cru.sender_id = u.id
                    LEFT JOIN `" . $modelChatAttachment::getTableName() . "` AS ca ON c.id = ca.chat_id
                    WHERE cr.id = '" . (int)$groupId . "'
                    ORDER BY c.updated_at ASC");
        }

        if (!empty($records)) {
            $storageFolderName         = (str_ireplace("\\", "/", $modelChatAttachment->folder));
            $storageFolderNameUser     = (str_ireplace("\\", "/", $model->profile));
            $storageFolderNameUserIcon = (str_ireplace("\\", "/", $model->profileIcon));

            foreach ($records as &$record) {
                if (!empty($record->created_at) && strtotime($record->created_at) > 0) {
                    $record->created_at = strtotime($record->created_at) * 1000;
                }

                if (!empty($record->updated_at) && strtotime($record->updated_at) > 0) {
                    $record->updated_at = strtotime($record->updated_at) * 1000;
                }

                if (!empty($record->attachment)) {
                    $record->attachment = Storage::disk($modelChatAttachment->fileSystem)->url($storageFolderName . '/' . $record->id . '/' . $record->attachment);
                }

                if (!empty($record->profile)) {
                    $record->profile = Storage::disk($model->fileSystem)->url($storageFolderNameUser . '/' . $record->profile);
                }

                if (!empty($record->profile_icon)) {
                    $record->profile_icon = Storage::disk($model->fileSystem)->url($storageFolderNameUserIcon . '/' . $record->profile_icon);;
                }
            }

            // Sortings.
            if (!empty($records)) {
                usort($records, function($a, $b) {
                    $t1 = $a->updated_at;
                    $t2 = $b->updated_at;

                    return $t1 - $t2;
                });
            }
        }

        return $this->returnSuccess(__('User chat history get successfully!'), $records);
    }
}
