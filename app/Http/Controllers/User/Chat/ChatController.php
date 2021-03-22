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
use App\ChatDelete;
use App\UserBlockProfile;
use LRedis;
use DB;
use Illuminate\Support\Facades\Storage;
// use Illuminate\Broadcasting\BroadcastManager;
// use Illuminate\Support\Facades\Event;
use Illuminate\Http\UploadedFile;
use Carbon\Carbon;
use Intervention\Image\ImageManagerStatic as Image;

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

        $groupId = $request->get('group_id', 122);

        return view('chat', compact('userId', 'sendBy', 'groupId'));
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
        $modelChatDelets     = new ChatDelete();

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
                              ->leftJoin($modelChatDelets::getTableName(), function($leftJoin) use($modelChats, $modelChatDelets, $userId) {
                                  $leftJoin->on($modelChats::getTableName() . '.id', '=', $modelChatDelets::getTableName() . '.chat_id')
                                           ->where($modelChatDelets::getTableName() . '.user_id', $userId);
                              })
                              ->leftJoin($modelChatAttachments::getTableName(), $modelChats::getTableName() . '.id', '=', $modelChatAttachments::getTableName() . '.chat_id')
                              ->whereRaw('(' . $modelChatRoomUsers::getTableName() . '.receiver_id = ' . $userId . ' OR ' . $modelChatRoomUsers::getTableName() . '.sender_id = ' . $userId . ')')
                              ->where($modelChatRooms::getTableName() . '.is_group', $modelChatRooms::IS_NOT_GROUP)
                              ->whereNull($modelChatDelets::getTableName() . '.id')
                              ->orderBy($modelChats::getTableName() . '.updated_at', 'ASC')
                              ->get();

        $returnDatas = [];

        if (!empty($records)) {
            $userIds = $records->pluck('sender_id');
            $userIds = $userIds->merge($records->pluck('receiver_id'));
            $users   = $model::selectRaw('*, profile as profile_image, profile_icon as profile_image_icon')->whereIn('id', $userIds->unique())->get()->keyBy('id');

            $records->map(function($data) use($users, $userId, $storageFolderName, $storageFolderNameIcon, $model, &$returnDatas) {

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

                if ($model->isBlocked($userId, $opponentId)) {
                    return false;
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
                              /*->leftJoin($modelChatDelets::getTableName(), function($leftJoin) use($modelChats, $modelChatDelets, $userId) {
                                  $leftJoin->on($modelChats::getTableName() . '.id', '=', $modelChatDelets::getTableName() . '.chat_id')
                                           ->where($modelChatDelets::getTableName() . '.user_id', $userId);
                              })*/
                              ->leftJoin($modelChatAttachments::getTableName(), $modelChats::getTableName() . '.id', '=', $modelChatAttachments::getTableName() . '.chat_id')
                              ->where($modelChatRooms::getTableName() . '.is_group', $modelChatRooms::IS_GROUP)
                              ->where($modelChatRoomUsers::getTableName() . '.sender_id', $userId)
                              // ->whereNull($modelChatDelets::getTableName() . '.id')
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
        $modelChatDelets     = new ChatDelete();
        $modelUserBlockProfiles = new UserBlockProfile();
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
            $records = DB::select("SELECT c.id, c.message, cru.sender_id, cru.receiver_id, c.created_at, c.updated_at, ca.mime_type, ca.attachment, ca.url, ca.address, ca.name, ca.contacts, CASE WHEN ca.mime_type != '' && ca.attachment != '' THEN 'attachment' WHEN ca.url != '' THEN 'location' WHEN ca.name && ca.contacts THEN 'contacts' WHEN c.message != '' THEN 'text' ELSE NULL END AS message_type, u.name as user_name, u.profile, u.profile_icon
                    FROM `" . $modelChatRoomUsers::getTableName() . "` AS cru
                    JOIN `" . $modelChats::getTableName() . "` AS c ON cru.id = c.chat_room_user_id
                    JOIN `" . $modelChatRooms::getTableName() . "` AS cr ON cru.chat_room_id = cr.id
                    JOIN `" . $model->getTableName() . "` AS u ON cru.sender_id = u.id
                    LEFT JOIN `" . $modelUserBlockProfiles::getTableName() . "` as ubp ON u.id = ubp.user_id AND (ubp.blocked_by = '" . $userId . "' OR ubp.blocked_by = '" . $receiverId . "') AND ubp.is_block = '" . (string)$modelUserBlockProfiles::IS_BLOCK . "'
                    LEFT JOIN `" . $modelChatDelets::getTableName() . "` as cd ON c.id = cd.chat_id AND cd.user_id = '" . $userId . "'
                    LEFT JOIN `" . $modelChatAttachment::getTableName() . "` AS ca ON c.id = ca.chat_id
                    WHERE ((cru.`sender_id` = '" . $userId . "' AND cru.`receiver_id` = '" . $receiverId . "') OR (cru.`sender_id` = '" . $receiverId . "' AND cru.`receiver_id` = '" . $userId . "')) AND cr.is_group = '" . $modelChatRooms::IS_NOT_GROUP . "' AND cd.id IS NULL AND ubp.id IS NULL
                    ORDER BY c.updated_at ASC");
        } else {
            $records = DB::select("SELECT c.id, c.message, cru.sender_id, cru.receiver_id, c.created_at, c.updated_at, ca.mime_type, ca.attachment, ca.url, ca.address, ca.name, ca.contacts, CASE WHEN ca.mime_type != '' && ca.attachment != '' THEN 'attachment' WHEN ca.url != '' THEN 'location' WHEN ca.name && ca.contacts THEN 'contacts' WHEN c.message != '' THEN 'text' ELSE NULL END AS message_type, u.name as user_name, u.profile, u.profile_icon
                    FROM `" . $modelChatRoomUsers::getTableName() . "` AS cru
                    JOIN `" . $modelChats::getTableName() . "` AS c ON cru.id = c.chat_room_user_id
                    JOIN `" . $modelChatRooms::getTableName() . "` AS cr ON cru.chat_room_id = cr.id
                    JOIN `" . $model->getTableName() . "` AS u ON cru.sender_id = u.id
                    LEFT JOIN `" . $modelChatDelets::getTableName() . "` as cd ON c.id = cd.chat_id AND cd.user_id = '" . $userId . "'
                    LEFT JOIN `" . $modelChatAttachment::getTableName() . "` AS ca ON c.id = ca.chat_id
                    WHERE cr.id = '" . (int)$groupId . "' AND cd.id IS NULL
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

    public function removeChat(Request $request)
    {
        $modelChats          = new Chat();
        $modelChatAttachment = new ChatAttachment();
        $modelChatRooms      = new ChatRoom();
        $modelChatRoomUsers  = new ChatRoomUser();
        $modelChatDelets     = new ChatDelete();
        $data                = $request->all();
        $now                 = new Carbon();

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

        if (!empty($receiverId)) {
            $chats = $modelChats::select($modelChats::getTableName() . '.id')
                                ->join($modelChatRoomUsers::getTableName(), $modelChats::getTableName() . '.chat_room_user_id', '=', $modelChatRoomUsers::getTableName() . '.id')
                                ->leftJoin($modelChatDelets::getTableName(), $modelChats::getTableName() . '.id', '=', $modelChatDelets::getTableName() . '.chat_id')
                                ->where(function($query) use($receiverId, $modelChatRoomUsers, $userId) {
                                        $query->where($modelChatRoomUsers::getTableName() . '.sender_id', $receiverId)
                                              ->orWhere($modelChatRoomUsers::getTableName() . '.receiver_id', $receiverId)
                                              ->whereRaw("(({$modelChatRoomUsers::getTableName()}.sender_id = {$userId} AND {$modelChatRoomUsers::getTableName()}.receiver_id = {$receiverId}) OR ({$modelChatRoomUsers::getTableName()}.receiver_id = {$userId} AND {$modelChatRoomUsers::getTableName()}.sender_id = {$receiverId}))");
                                    })
                                    ->whereNull($modelChatDelets::getTableName() . '.id')
                                ->get();
        } else {
            $chats = $modelChats::select($modelChats::getTableName() . '.id')
                                ->join($modelChatRoomUsers::getTableName(), $modelChats::getTableName() . '.chat_room_user_id', '=', $modelChatRoomUsers::getTableName() . '.id')
                                ->leftJoin($modelChatDelets::getTableName(), function($leftJoin) use($modelChats, $modelChatDelets, $userId) {
                                    $leftJoin->on($modelChats::getTableName() . '.id', '=', $modelChatDelets::getTableName() . '.chat_id')
                                             ->where($modelChatDelets::getTableName() . '.user_id', $userId);
                                })
                                ->where($modelChatRoomUsers::getTableName() . '.chat_room_id', $groupId)
                                ->whereNull($modelChatDelets::getTableName() . '.id')
                                ->get();
        }

        if (!empty($chats) && !$chats->isEmpty()) {
            $chatIds      = $chats->pluck('id');
            $errorMessage = NULL;
            $delete       = [];

            foreach ($chatIds as $index => $chatId) {
                $delete[$index] = [
                    'chat_id'    => $chatId,
                    'user_id'    => $userId,
                    'created_at' => $now,
                    'updated_at' => $now
                ];

                $validator = $modelChatDelets->validators($delete[$index]);

                if ($validator->fails()) {
                    $errorMessage = $validator->errors()->first();
                    break;
                }
            }

            if (!empty($errorMessage)) {
                return $this->returnError($errorMessage);
            } elseif (!empty($delete)) {
                if ($modelChatDelets::insert($delete)) {
                    return $this->returnSuccess(__('User chat removed successfully!'));
                }
            }
        }

        // return $this->returnError(__('Something went wrong!'));
        return $this->returnSuccess(__('User chat removed successfully!'));
    }

    //function to get all the users for group
    public function getAllUsersList(Request $request) {
        $users = User::select('id', 'name', 'profile_pic')->where('is_admin', 0)->where('id', '!=', $request->user_id)->get();
        return $this->returnSuccess(__('Users fetched successfully!'), $users);
    }

    //function to create the chat group
    public function createChatGroup(Request $request) {
        $chat_room = new ChatRoom();
        $chat = new Chat();
        $data  = $request->all();
        
        $data['uuid'] = $chat->generateUuid(10);
        $data['is_group'] = $chat_room::IS_GROUP;

        if($request->has('users')) {
            array_push($data['users'], $request->user_id);
        } else {
            $data['users'] = [$request->user_id];
        }

        $validator = $chat_room->validators($data);
        if ($validator->fails()) {
            return $this->returnError($validator->errors()->first());
        }

        if($request->has('chat_room_id') && !empty($request->chat_room_id)) {
            $chat_room = ChatRoom::find($request->chat_room_id);
            $prevIcon = $chat_room->group_icon_actual;
            $chat_room_data = $chat_room->ChatRoomUsers->pluck('id')->toArray();
        }

        $chat_room->uuid = $data['uuid'];
        $chat_room->title = $request->title;
        $chat_room->is_group = $data['is_group'];

        $save = $chat_room->save();

        if ($save && array_key_exists('group_icon', $data) && $data['group_icon'] instanceof UploadedFile) {
            $id = $chat_room->id;

            if(!empty($prevIcon)) {
                $array = explode('/', $prevIcon);
                $key = array_key_last($array);
                $image = $array[$key];
                Storage::delete($chat_room->fileSystem . '/'. $chat_room->folder .'/' .$id .'/'. $image);
                Storage::delete($chat_room->fileSystem . '/'. $chat_room->folder .'/' .$id .'/icon//'. $image);
            }

            $attachment = $data['group_icon'];
            $pathInfos = pathinfo($attachment->getClientOriginalName());

            if (!empty($pathInfos['extension'])) {
                $thumb_folder = $chat_room->folder . '/' . $id . '/icon//';
                $folder = $chat_room->folder . '/' . $id;

                if (!empty($folder)) {
                    $fileName  = (empty($pathInfos['filename']) ? time() : $pathInfos['filename']) . '_' . time() . '.' . $pathInfos['extension'];
                    $storeFile = $attachment->storeAs($folder, $fileName, $chat_room->fileSystem);

                    $thumb_image = Image::make($data['group_icon'])->resize(100, 100);
                    Storage::disk($chat_room->fileSystem)->put($thumb_folder . $fileName, $thumb_image->encode());

                    if ($storeFile) {
                        $chat_room = $chat_room->find($id);

                        $chat_room->group_icon = $fileName;
                        $chat_room->group_icon_actual = $fileName;

                        $chat_room->save();
                    }
                }
            }
        }
        
        $chat_users_list = array();
        if(!empty($data['users'])) {
            foreach($data['users'] as $user) {
                if($request->has('chat_room_id')) {
                    $chat_users = $chat_room->ChatRoomUsers->pluck('sender_id')->toArray();
                    $chat_room_users = new ChatRoomUser();
                    if(in_array((int)$user, $chat_users)) {
                        $chat_room_users = ChatRoomUser::where('chat_room_id', $chat_room->id)->where('sender_id', $user)->first();
                    }
                } else {
                    $chat_room_users = new ChatRoomUser();
                }
                $chat_room_users->chat_room_id = $chat_room->id;
                $chat_room_users->sender_id = $user;
                $chat_room_users->save();
                $chat_users_list[] = $chat_room_users->id;
            }
            if($request->has('chat_room_id')) {
                $deleted_users = array_diff($chat_room_data, $chat_users_list);
                if(!empty($deleted_users)) {
                    ChatRoomUser::destroy($deleted_users);
                }
            }
        }

        $chat_room_details = ChatRoom::with(['chatRoomUsers.Users' => function($q) {
                                $q->select('id', 'name', 'profile_pic');
                            }])
                            ->with(['chatRoomUsers' => function($q) use ($request) {
                                $q->where('sender_id', '!=', $request->user_id);
                            }])
                            ->where('id', $chat_room->id)
                            ->get();
                            
        $chat_room_details->each(function($row){
            $row->chatRoomUsers->each(function($userRow) {
                $userRow->Users->setHidden(['encrypted_user_id', 'permissions', 'total_notifications', 'total_read_notifications', 'total_unread_notifications']);
            });
        });
        if ($request->has('chat_room_id')) {
            return $this->returnSuccess(__('Chat group edited successfully!'), $chat_room_details);    
        }
        return $this->returnSuccess(__('Chat group created successfully!'), $chat_room_details);
    }

    //Function to add the user in group
    public function addUserToChatGroup(Request $request) {
        $chat_room_user = new ChatRoomUser();
        $data  = $request->all();

        if(!$request->has('userId') || empty($request->userId)) {
            return $this->returnError(__('UserId is mandatory.'));
        }

        $validator = $chat_room_user->validators($data);
        if ($validator->fails()) {
            return $this->returnError($validator->errors()->first());
        }

        foreach($request->userId as $user) {
            $checkExist = $chat_room_user::where('chat_room_id', $request->chat_room_id)->where('sender_id', $user)->count();
            if(!$checkExist) {
                $chat_room_users = new ChatRoomUser();
                $chat_room_users->chat_room_id = $request->chat_room_id;
                $chat_room_users->sender_id = $user;
                $chat_room_users->save();
            }
        }
        
        return $this->returnSuccess(__('User successfully added to Chat group!'));
    }

    //Function to remove the user from group
    public function removeUserFromChatGroup(Request $request) {
        $chat_room_user = new ChatRoomUser();
        $data  = $request->all();

        if(!$request->has('userId') || empty($request->userId)) {
            return $this->returnError(__('UserId is mandatory.'));
        }

        $validator = $chat_room_user->validators($data);
        if ($validator->fails()) {
            return $this->returnError($validator->errors()->first());
        }

        foreach($request->userId as $user) {
            $chatUser = $chat_room_user::where('chat_room_id', $request->chat_room_id)->where('sender_id', $user)->first();
            if(!empty($chatUser)) {
                $chatUser->delete();
            }
        }
        
        return $this->returnSuccess(__('Users successfully removed from Chat group!'));
    }

    //Function to get the chat group details
    public function getChatGroupDetails(Request $request, $id) {
        $chat_room_details = ChatRoom::with(['chatRoomUsers.Users' => function($q) {
                                $q->select('id', 'name', 'profile_pic');
                            }])
                            ->where('id', $id)
                            ->get();
        $chat_room_details->each(function($row){
            $row->chatRoomUsers->each(function($userRow) {
                $userRow->Users->setHidden(['encrypted_user_id', 'permissions', 'total_notifications', 'total_read_notifications', 'total_unread_notifications']);
            });
        });
        return $this->returnSuccess(__('Chat group details fetched successfully!'), $chat_room_details);
    }
}
