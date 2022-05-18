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
use Illuminate\Support\Facades\Validator;
use App\ReportChatRoom;
use App\Jobs\SendChatMessageNotification;
use App\ApiKey;
use App\Notification;

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

            return $this->returnSuccess(__(MESSAGE_SEND), $chat);
        }

        return $this->returnError(__(SOMETHING_WENT_WRONG));
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
        $isGroup             = false;

        if (empty($userId)) {
            return $this->returnError(__(USERID_REQUIRED));
        }

        if (empty($receiverId) && empty($groupId)) {
            return $this->returnError(__(RECEIVER_GROUP_ID_REQUIRED));
        }

        if (!empty($data['name']) && empty($data['contacts'])) {
            return $this->returnError(__(CONTACT_REQUIRED));
        } elseif (!empty($data['contacts']) && empty($data['name'])) {
            return $this->returnError(__(NAME_REQUIRED));
        }/* elseif (!empty($data['url']) && empty($data['address'])) {
            return $this->returnError(__('Address required if URL present.'));
        } elseif (empty($data['url']) && !empty($data['address'])) {
            return $this->returnError(__('URL required if Address present.'));
        } */elseif (empty($data['url']) && empty($data['attachment']) && empty($data['name']) && empty($data['contacts'])) {
            return $this->returnError(__(ATTACHMENT_URL_CONTACTS_PROVIDE));
        }

        if (!empty($data)) {

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
                    return $this->returnError(__(GROUP_NOT_FOUND));
                }

                $chatRoomUser = $modelChatRoomUser::where('chat_room_id', (int)$groupId)->where('sender_id', (int)$userId)->first();

                if (empty($chatRoomUser)) {
                    return $this->returnError(__(USER_NOT_ADDED_TO_GROUP));
                }

                $chatRoomUserId = $chatRoomUser->id;
                $chatRoomId     = $groupId;

                $isGroup    = true;
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
                    $original_attachment_name = $pathInfos['filename'] . '.' . $pathInfos['extension'];

                    if (!empty($pathInfos['extension'])) {
                        if ($pathInfos['extension'] == 'm4a') {
                            $mimeType = "audio/mp4";
                        }

                        $fileName  = (empty($pathInfos['filename']) ? time() : $pathInfos['filename']) . '_' . time() . '.' . $pathInfos['extension'];
                        $fileName  = removeSpaces($fileName);
                        $storeFile = $attachment->storeAs($modelChatAttachment->folder . '\\' . $chatId, $fileName, $modelChatAttachment->fileSystem);

                        if ($storeFile) {
                            $isInserted = $modelChatAttachment->create(['mime_type' => $mimeType, 'attachment' => $fileName, 'chat_id' => $chatId, 'original_attachment_name' => $original_attachment_name]);

                            if ($isInserted) {
                                // Send push notification if user is not online.
                                $request->merge(["message" => __('Attachment')]);
                                $request->merge(["from_user_id" => $userId]);

                                if ($isGroup) {
                                    $request->merge(["room_id" => $chatRoomId]);

                                    $this->chatMessageGroup($request);
                                } else {
                                    $request->merge(["user_id" => $receiverId]);

                                    $this->chatMessage($request);
                                }

                                return $this->returnSuccess(__(ATTACHMENT_ADDED), $isInserted);
                            }
                        }
                    }

                    return $this->returnError(__(ATTACHMENT_NOT_ADDED));

                } elseif (!empty($data['url'])) {
                    $address = !empty($data['address']) ? $data['address'] : NULL;

                    $isInserted = $modelChatAttachment->create(['url' => $data['url'], 'address' => $address, 'chat_id' => $chatId]);

                    if ($isInserted) {
                        // Send push notification if user is not online.
                        $request->merge(["message" => __('Location')]);
                        $request->merge(["from_user_id" => $userId]);

                        if ($isGroup) {
                            $request->merge(["room_id" => $chatRoomId]);

                            $this->chatMessageGroup($request);
                        } else {
                            $request->merge(["user_id" => $receiverId]);

                            $this->chatMessage($request);
                        }

                        return $this->returnSuccess(__(URL_ADDRESS_ADDED), $isInserted);
                    }

                    return $this->returnError(__(URL_ADDRESS_NOT_ADDED));

                } elseif (!empty($data['name']) && !empty($data['contacts'])) {
                    $isInserted = $modelChatAttachment->create(['name' => $data['name'], 'contacts' => $data['contacts'], 'chat_id' => $chatId]);

                    if ($isInserted) {
                        // Send push notification if user is not online.
                        $request->merge(["message" => __('Contacts')]);
                        $request->merge(["from_user_id" => $userId]);

                        if ($isGroup) {
                            $request->merge(["room_id" => $chatRoomId]);

                            $this->chatMessageGroup($request);
                        } else {
                            $request->merge(["user_id" => $receiverId]);

                            $this->chatMessage($request);
                        }

                        return $this->returnSuccess(__(GROUP_CONTACT_ADDED), $isInserted);
                    }

                    return $this->returnError(__(GROUP_CONTACT_NOT_ADDED));

                }
            }

            if (!$isInserted) {
                $model->find($chatId)->delete();
            }
        }

        return $this->returnError(__(SOMETHING_WENT_WRONG));
    }

    public function getUsersList(Request $request)
    {
        $model                = new User();
        $modelChatRoomUsers   = new ChatRoomUser();
        $modelChats           = new Chat();
        $modelChatRooms       = new ChatRoom();
        $modelChatAttachments = new ChatAttachment();
        $modelChatDelets      = new ChatDelete();

        $userId     = (int)$request->get('user_id', false);

        $chatRoomId = (int)$request->get('chat_room_id', false);

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

        $records = $modelChats::selectRaw("{$modelChats::getTableName()}.id as chat_id, {$modelChatRoomUsers::getTableName()}.sender_id, {$modelChatRoomUsers::getTableName()}.receiver_id, {$modelChatRooms::getTableName()}.id as chat_room_id, {$modelChatRooms::getTableName()}.created_by_admin, {$modelChatRooms::getTableName()}.created_by, {$modelChats::getTableName()}.updated_at, CASE WHEN {$modelChatAttachments::getTableName()}.mime_type != '' && {$modelChatAttachments::getTableName()}.attachment != '' THEN 'Attachment Sent' WHEN {$modelChatAttachments::getTableName()}.url != '' THEN 'URL Sent' WHEN {$modelChatAttachments::getTableName()}.name != '' && {$modelChatAttachments::getTableName()}.contacts != '' THEN 'Contact Sent' ELSE {$modelChats::getTableName()}.message END AS recent_message, {$modelChatRooms::getTableName()}.is_group, {$modelChatRooms::getTableName()}.title")
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
                              ->orderBy($modelChats::getTableName() . '.updated_at', 'ASC');

        if (!empty($chatRoomId)) {
            $records->where($modelChatRooms::getTableName() . '.id', $chatRoomId);
        }

        $records = $records->get();

        $returnDatas = [];

        if (!empty($records)) {
            $userIds = $records->pluck('sender_id');
            $userIds = $userIds->merge($records->pluck('receiver_id'));
            $users   = $model::select('id', 'name', 'profile', 'profile_icon')->whereIn('id', $userIds->unique())->where('is_accepted' , 1)->get()->keyBy('id');

            $records->map(function($data) use($users, $userId, $storageFolderName, $storageFolderNameIcon, $model, &$returnDatas) {

                $user = $opponentId = false;

                if ($data->sender_id == $userId) {
                    if (!empty($users[$data->receiver_id])) {
                        $opponentId = $data->receiver_id;
                        $user       = $users[$data->receiver_id];
                    }
                } elseif (!empty($users[$data->sender_id])) {
                    $opponentId = $data->sender_id;
                    $user       = $users[$data->sender_id];
                }

                if (!$opponentId || $model->isBlocked($userId, $opponentId)) {
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
                        'profile'           => !empty($user->getAttribute('profile')) ? $user->getAttribute('profile') : NULL,
                        'profile_icon'      => !empty($user->getAttribute('profile_icon')) ? $user->getAttribute('profile_icon') : NULL,
                        'recent_time'       => strtotime($data->updated_at) * 1000,
                        'recent_message'    => $data->recent_message,
                        'is_group'          => $data->is_group,
                        'is_online'         => $user->is_online,
                        'socket_id'         => $user->socket_id,
                        'title'             => $data->title,
                        'created_by_admin'  => $data->created_by_admin,
                        'created_by'        => $data->created_by
                    ];
                }
            });
        }

        $records = $modelChatRooms::selectRaw("{$modelChats::getTableName()}.id as chat_id, {$modelChatRooms::getTableName()}.id, {$modelChatRooms::getTableName()}.created_by_admin, {$modelChatRooms::getTableName()}.created_by, {$modelChatRooms::getTableName()}.id as chat_room_id, {$modelChats::getTableName()}.updated_at, CASE WHEN {$modelChatAttachments::getTableName()}.mime_type != '' && {$modelChatAttachments::getTableName()}.attachment != '' THEN 'Attachment Sent' WHEN {$modelChatAttachments::getTableName()}.url != '' THEN 'URL Sent' WHEN {$modelChatAttachments::getTableName()}.name != '' && {$modelChatAttachments::getTableName()}.contacts != '' THEN 'Contact Sent' ELSE {$modelChats::getTableName()}.message END AS recent_message, {$modelChatRooms::getTableName()}.is_group, {$modelChatRooms::getTableName()}.title, {$modelChatRooms::getTableName()}.group_icon, {$modelChatRooms::getTableName()}.group_icon_actual, {$modelChatDelets::getTableName()}.id as chat_deleted_id")
                              ->join($modelChatRoomUsers::getTableName(), $modelChatRooms::getTableName() . '.id', '=', $modelChatRoomUsers::getTableName() . '.chat_room_id')
                              ->leftJoin($modelChats::getTableName(), $modelChatRooms::getTableName() . '.id', '=', $modelChats::getTableName() . '.chat_room_id')
                              ->leftJoin($modelChatDelets::getTableName(), function($leftJoin) use($modelChats, $modelChatDelets, $userId) {
                                  $leftJoin->on($modelChats::getTableName() . '.id', '=', $modelChatDelets::getTableName() . '.chat_id')
                                           ->where($modelChatDelets::getTableName() . '.user_id', $userId);
                              })
                              ->leftJoin($modelChatAttachments::getTableName(), $modelChats::getTableName() . '.id', '=', $modelChatAttachments::getTableName() . '.chat_id')
                              ->where($modelChatRooms::getTableName() . '.is_group', $modelChatRooms::IS_GROUP)
                              ->where($modelChatRoomUsers::getTableName() . '.sender_id', $userId)
                              // ->whereNull($modelChatDelets::getTableName() . '.id')
                              // ->whereNull($modelChats::getTableName() . '.deleted_at')
                              ->orderBy($modelChats::getTableName() . '.updated_at', 'ASC');

        if (!empty($chatRoomId)) {
            $records->where($modelChatRooms::getTableName() . '.id', $chatRoomId);
        }

        $records = $records->get();

        $returnGroupDatas = [];

        if (!empty($records)) {
            $records->map(function($data) use(&$returnGroupDatas, $modelChatRooms) {
                $returnGroupDatas[$data->chat_room_id] = [
                    'chat_id'            => $data->chat_id,
                    'chat_room_id'       => $data->chat_room_id,
                    'recent_time'        => empty($data->chat_deleted_id) ? (strtotime($data->updated_at) * 1000) : 0,
                    'recent_message'     => empty($data->chat_deleted_id) ? $data->recent_message : '',
                    'is_group'           => $data->is_group,
                    'title'              => $data->title,
                    'group_icon'         => $data->group_icon,
                    'group_icon_actual'  => $data->group_icon_actual,
                    'total_participants' => $modelChatRooms->totalGroupParticipants($data->chat_room_id),
                    'created_by_admin'  => $data->created_by_admin,
                    'created_by'        => $data->created_by
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

        $blockedUser = UserBlockProfile::where('blocked_by', $userId)->where('is_block' , '1')->pluck('user_id')->toArray();
        $data = array();
        foreach($return as $key => $returnData) {
            $chat_room_id = $returnData['chat_room_id'];
            if(!empty($blockedUser)) {
                $participants = $model::with(['ChatRoomsUsers'])->whereHas('ChatRoomsUsers', 
                function($q) use ($chat_room_id , $blockedUser) {
                    $q->where('chat_room_id', $chat_room_id)
                    ->whereNotIn('id', $blockedUser);
                    }
                )
                ->whereNotNull('profile')
                ->take(3)
                ->pluck('profile_icon')
                ->toArray();
            } else {
                $participants = $model::with(['ChatRoomsUsers'])->whereHas('ChatRoomsUsers', 
                                function($q) use ($chat_room_id) {
                                    $q->where('chat_room_id', $chat_room_id);
                                }
                            )
                            ->whereNotNull('profile')
                            ->take(3)
                            ->pluck('profile_icon')
                            ->toArray();
            }
            
            $returnData['participants'] = $participants;
            $returnData['total_participants'] = count($participants);
            $data[] = $returnData;
        }

        return $this->returnSuccess(__(USER_CHAT_LIST_GET), $data);
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
            $records = DB::select("SELECT c.id, c.message, cru.sender_id, cru.receiver_id, c.created_at, c.updated_at, ca.mime_type, ca.attachment, ca.original_attachment_name as attachment_name, ca.url, ca.address, ca.name, ca.contacts, CASE WHEN ca.mime_type != '' && ca.attachment != '' THEN 'attachment' WHEN ca.url != '' THEN 'location' WHEN ca.name && ca.contacts THEN 'contacts' WHEN c.message != '' THEN 'text' ELSE NULL END AS message_type, u.name as user_name, u.profile, u.profile_icon
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
            $records = DB::select("SELECT c.id, c.message, cru.sender_id, cru.receiver_id, c.created_at, c.updated_at, ca.mime_type, ca.attachment, ca.original_attachment_name as attachment_name, ca.url, ca.address, ca.name, ca.contacts, CASE WHEN ca.mime_type != '' && ca.attachment != '' THEN 'attachment' WHEN ca.url != '' THEN 'location' WHEN ca.name && ca.contacts THEN 'contacts' WHEN c.message != '' THEN 'text' ELSE NULL END AS message_type, u.name as user_name, u.profile, u.profile_icon
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
            $dbReceiverId              = collect($records)->pluck('receiver_id');

            // Get receiver user.
            $receiverUsers = $model::select('id', 'profile', 'profile_icon')->whereIn('id', $dbReceiverId)->where('is_accepted' , 1)->get()->keyBy('id');

            foreach ($records as &$record) {
                $receiverUser = (!empty($receiverUsers[$record->receiver_id])) ? $receiverUsers[$record->receiver_id] : [];

                if (!empty($record->created_at) && strtotime($record->created_at) > 0) {
                    $record->created_at = strtotime($record->created_at) * 1000;
                }

                if (!empty($record->updated_at) && strtotime($record->updated_at) > 0) {
                    $record->updated_at = strtotime($record->updated_at) * 1000;
                }

                if (!empty($record->attachment)) {
                    $record->attachment = Storage::disk($modelChatAttachment->fileSystem)->url($storageFolderName . '/' . $record->id . '/' . $record->attachment);
                }

                if (!empty($receiverUser) && !empty($receiverUser->getAttribute('profile'))) {
                    $record->profile = $receiverUser->getAttribute('profile');
                } else {
                    $record->profile = NULL;
                }

                if (!empty($receiverUser) && !empty($receiverUser->getAttribute('profile_icon'))) {
                    $record->profile_icon = $receiverUser->getAttribute('profile_icon');
                } else {
                    $record->profile_icon = NULL;
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
        return $this->returnSuccess(__(USER_HISTORY_GET), $records);
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
                                ->whereNull($modelChatDelets::getTableName() . '.id');

            if ($userId == $receiverId) {
                $chats->where(function($query) use($receiverId, $modelChatRoomUsers, $userId) {
                    $query->whereRaw("(({$modelChatRoomUsers::getTableName()}.sender_id = {$userId} AND {$modelChatRoomUsers::getTableName()}.receiver_id = {$receiverId}) OR ({$modelChatRoomUsers::getTableName()}.receiver_id = {$userId} AND {$modelChatRoomUsers::getTableName()}.sender_id = {$receiverId}))");
                });
            } else {
                $chats->where(function($query) use($receiverId, $modelChatRoomUsers, $userId) {
                    $query->where($modelChatRoomUsers::getTableName() . '.sender_id', $receiverId)
                          ->orWhere($modelChatRoomUsers::getTableName() . '.receiver_id', $receiverId)
                          ->whereRaw("(({$modelChatRoomUsers::getTableName()}.sender_id = {$userId} AND {$modelChatRoomUsers::getTableName()}.receiver_id = {$receiverId}) OR ({$modelChatRoomUsers::getTableName()}.receiver_id = {$userId} AND {$modelChatRoomUsers::getTableName()}.sender_id = {$receiverId}))");
                });
            }

            $chats = $chats->get();
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

            // Remove chats from Chat table.
            // $modelChats::whereIn('id', $chatIds)->delete();

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
                    return $this->returnSuccess(__(USER_CHAT_REMOVED));
                }
            }
        }

        // return $this->returnError(__(SOMETHING_WENT_WRONG));
        return $this->returnSuccess(__(USER_CHAT_REMOVED));
    }

    //function to get all the users for group
    public function getAllUsersList(Request $request) {
        $per_page = $request->has('per_page') ? $request->per_page : 10;
        $offset = $request->has('offset') ? (int)$request->offset : 0;
        $search = $request->has('search') ? $request->search : '';
        $status = 200;
        $next_offset = $offset + $per_page;

        $users_count =  User::where('is_admin', 0)->where('id', '!=', $request->user_id)->count();
        $search_users_count = 0;

        $users = User::select('id', 'name', 'profile', 'profile_icon')
                    ->where('is_admin', 0)
                    ->where('id', '!=', $request->user_id);
        if(!empty($search)) {
            $users = $users->where('name', 'like', $search . '%');
            $search_users_count = $users->count();
        }


        $blockedUser = UserBlockProfile::where('blocked_by', $request->user_id)->where('is_block' , '1')->pluck('user_id')->toArray();
        if(!empty($blockedUser)) {
            $users = $users->whereNotIn('id' , $blockedUser);
        }

        $users = $users->skip($offset)
                    ->take($per_page)
                    ->get();
        $users->each(function($userRow) {
            $userRow->setHidden(['encrypted_user_id', 'permissions', 'total_notifications', 'total_read_notifications', 'total_unread_notifications']);
        });

        //TO reset the next_offset if no users in list
        if(!empty($search) && ($next_offset >= $search_users_count)) {
            $next_offset = $offset;
        } else {
            if($next_offset >= $users_count) {
                $next_offset = $offset;
            }
        }
        
        return response()->json([
            'code' => $status,
            'msg'  => __('Users fetched successfully!'),
            'current_offset' => $offset,
            'next_offset' => $next_offset,
            'per_page' => $per_page,
            'total_users' => $users_count,
            'search_users_count' => $search_users_count,
            'data' => $users
        ], 200);
    }

    //function to create the chat group
    public function createChatGroup(Request $request) {
        $chat_room = new ChatRoom();
        $chat = new Chat();
        $chatRoomUser = new ChatRoomUser();
        $data  = $request->all();

        if (!empty($data['users'])) {
            if (is_string($data['users'])) {
                $data['users'] = explode(",", $data['users']);
            }
        }
        
        $data['uuid'] = $chat->generateUuid(10);
        $data['is_group'] = $chat_room::IS_GROUP;
        $data['created_by_admin'] = 0;
        $data['created_by'] = $request->user_id;

        if ($request->has('users')) {
            array_push($data['users'], $request->user_id);
        }/* else {
            $data['users'] = [$request->user_id];
        }*/

        $validator = $chat_room->validators($data);
        if ($validator->fails()) {
            return $this->returnError($validator->errors()->first());
        }

        $validator = $chatRoomUser->validatorUsers(['sender_id' => $data['users']]);
        if ($validator->fails()) {
            return $this->returnError($validator->errors()->first());
        }

        if($request->has('chat_room_id') && !empty($request->chat_room_id)) {
            $chat_room = ChatRoom::find($request->chat_room_id);
            if(empty($chat_room)) {
                return $this->returnError(__('Chatroom not found. Check chat_room_id in request.'));
            }
            $prevIcon = $chat_room->group_icon_actual;
            $chat_room_data = $chat_room->ChatRoomUsers->pluck('id')->toArray();
        }

        $chat_room->uuid = $data['uuid'];
        $chat_room->title = $request->title;
        $chat_room->description = !empty($request->description) ? $request->description : NULL;
        $chat_room->is_group = $data['is_group'];
        $chat_room->group_type = isset($data['group_type']) ? $data['group_type'] : 1;
        $chat_room->city_id = !empty($data['city_id']) ? $data['city_id'] : null;
        $chat_room->country_id = !empty($data['country_id']) ? $data['country_id'] : null;
        
        if(!$request->has('chat_room_id')) {
            $chat_room->created_by_admin = $data['created_by_admin'];
            $chat_room->created_by = $data['created_by'];
        }
        

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
                    $fileName  = removeSpaces($fileName);
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
                                $q->select('id', 'name', 'profile', 'profile_icon');
                            }])
                            ->with(['chatRoomUsers' => function($q) use ($request) {
                                $q->where('sender_id', '!=', $request->user_id);
                            }])
                            ->where('id', $chat_room->id)
                            ->get();
                            
        $chat_room_details->each(function($row){
            $row->chatRoomUsers->each(function($userRow) {
                if (empty($userRow->Users)) {
                    return false;
                }

                $userRow->Users->setHidden(['encrypted_user_id', 'permissions', 'total_notifications', 'total_read_notifications', 'total_unread_notifications']);
            });
        });
        if ($request->has('chat_room_id')) {
            return $this->returnSuccess(__(CHAT_GROUP_UPDATED), $chat_room_details);    
        }
        return $this->returnSuccess(__(CHAT_GROUP_CREATED), $chat_room_details);
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
        
        return $this->returnSuccess(__(USER_ADDED_TO_CHAT));
    }

    //Function to remove the user from group
    public function removeUserFromChatGroup(Request $request) {
        $chat_room_user = new ChatRoomUser();
        $data  = $request->all();

        if(!$request->has('userId') || empty($request->userId)) {
            return $this->returnError(__(USERID_REQUIRED));
        }

        $validator = $chat_room_user->validators($data);
        if ($validator->fails()) {
            return $this->returnError($validator->errors()->first());
        }
        $loggedInUserDetails = ApiKey::where('key', $request->header('api_key'))->first();
        $chatRoomDetails = ChatRoom::find($request->chat_room_id);
        if($chatRoomDetails->created_by != $loggedInUserDetails->user_id) {
            return $this->returnError(__(NO_RIGHTS));
        }

        foreach($request->userId as $user) {
            $chatUser = $chat_room_user::where('chat_room_id', $request->chat_room_id)->where('sender_id', $user)->first();
            if(!empty($chatUser)) {
                $chatUser->delete();
            }
        }
        
        return $this->returnSuccess(__(USER_REMOVED));
    }

    //Function to get the chat group details
    public function getChatGroupDetails(Request $request, $id) {
        $chatRoomDetails = ChatRoom::with(['chatRoomUsers.Users' => function($q) {
                                $q->select('id', 'name', 'profile', 'profile_icon');
                            }])
                            ->where('id', $id)
                            ->get();

        $chatRoomDetails->each(function(&$row, $index) use($chatRoomDetails) {
            $userDetails = [];

            $row->chatRoomUsers->each(function($userRow) use(&$userDetails) {
                if (empty($userRow->Users)) {
                    return false;
                }

                $userRow->Users->setHidden(['encrypted_user_id', 'permissions', 'total_notifications', 'total_read_notifications', 'total_unread_notifications']);

                if ($userRow->Users->is_blocked == UserBlockProfile::IS_NOT_BLOCK) {
                    $userDetails[] = $userRow;
                }
            });

            unset($chatRoomDetails[$index]->chatRoomUsers);

            $row->chat_room_users = $userDetails;
        });

        return $this->returnSuccess(__(CHAT_FETCHED), $chatRoomDetails);
    }

    //Function to exit the chat group
    public function exitChatGroup(Request $request) {
        $requestData = $request->toArray();

        $validator = Validator::make($requestData, [
            'chat_group_id' => 'required|exists:chat_rooms,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 500, 'message' => $validator->errors()->first()]);
        }
        $chat_room_details = ChatRoomUser::where([
            'chat_room_id' => $request->chat_group_id,
            'sender_id' => $request->user_id
        ])->first();
        if(!empty($chat_room_details)) {
            if($chat_room_details->delete()) {
                return $this->returnSuccess(__(EXIT_GROUP));
            }
        } else {
            return $this->returnError(__(NOT_ASSOCIATED));
        }
        return $this->returnError(__(SOMETHING_WENT_WRONG));
    }

    //function to report the chat-group
    public function reportChatGroup(Request $request) {
        $report = new ReportChatRoom();
        $data  = $request->all();
        
        $data['user_id'] = $request->user_id;
        
        $validator = $report->validator($data);
        if ($validator->fails()) {
            return $this->returnError($validator->errors()->first());
        }
        
        $report->user_id = $request->user_id;
        $report->chat_room_id = $request->chat_room_id;
        if($request->has('description')) {
            $report->description = $request->description;
        }
        $report->save();
        return $this->returnSuccess(__(REPORTED_GROUP));
    }

    //Function to delete the chat group
    public function deleteChatGroup(Request $request) {
        $chat_room = new ChatRoom();
        $chat_room_user = new ChatRoomUser();
        $user_id = $request->user_id;
        $chat_room_id = $request->chat_room_id;

        if(!empty($chat_room_id)) {
            $chat_room_details = $chat_room::where('created_by', $user_id)->where('created_by_admin', 0)->find($chat_room_id);
            if(!empty($chat_room_details)) {
                
                //To remove the icos for chat
                Storage::deleteDirectory($chat_room->fileSystem . '/'. $chat_room->folder .'/' .$chat_room_id);
                //To remove the group users
                $chat_room_user->where('chat_room_id', $chat_room_id)->delete();
                //Remove chat group
                $chat_room_details->delete();
                
                return $this->returnSuccess(__(REMOVED_GROUP));
            }
            return $this->returnError(__(NO_RIGHTS_REMOVE_GROUP));
        }
        return $this->returnError(__(SOMETHING_WENT_WRONG));
    }

    public function chatMessage(Request $request)
    {
        $userId     = (int)$request->get('user_id', false);
        $message    = $request->get('message', NULL);
        $fromUserId = (int)$request->get('from_user_id', false);
        $chatRoomId = (int)$request->get('chat_room_id', false);

        if (!empty($userId) && !empty($message) && !empty($fromUserId) && !empty($chatRoomId)) {
            $apiKey = ApiKey::getApiKey($userId);

            if (!empty($apiKey)) {
                $chatUsersList       = $this->callSelfApiGet(route('user.chat.users.list'), $apiKey, ['chat_room_id' => $chatRoomId]);

                $dataPayload['data'] = !empty($chatUsersList['data']) ? json_encode(reset($chatUsersList['data'])) : json_encode([]);

                $dataPayload['notification_type'] = Notification::NOTIFICATION_CHAT;

                SendChatMessageNotification::dispatch($userId, $message, $fromUserId, $dataPayload)->delay(now()->addSeconds(2));

                return true;
            }
        }

        return false;
    }

    public function chatMessageGroup(Request $request)
    {
        $roomId     = (int)$request->get('room_id', false);
        $message    = $request->get('message', NULL);
        $fromUserId = (int)$request->get('from_user_id', false);

        if (!empty($roomId) && !empty($message) && !empty($fromUserId)) {
            $chatRoomUsers = ChatRoomUser::where('chat_room_id', $roomId)->where('sender_id', '!=', $fromUserId)->get();

            if (!empty($chatRoomUsers) && !$chatRoomUsers->isEmpty()) {
                $apiKey = ApiKey::getApiKey($fromUserId);

                if (!empty($apiKey)) {
                    $chatUsersList       = $this->callSelfApiGet(route('user.chat.users.list'), $apiKey, ['chat_room_id' => $roomId]);

                    $dataPayload['data'] = !empty($chatUsersList['data']) ? json_encode(reset($chatUsersList['data'])) : json_encode([]);

                    $dataPayload['notification_type'] = Notification::NOTIFICATION_CHAT_GROUP;

                    foreach ($chatRoomUsers as $chatRoomUser) {
                        $userId = $chatRoomUser->sender_id;

                        SendChatMessageNotification::dispatch($userId, $message, $fromUserId, $dataPayload)->delay(now()->addSeconds(2));
                    }

                    return true;
                }
            }
        }

        return false;
    }

    //Function to get the public groups listing
    public function getPublicGroupLists(Request $request) {
        $user_id = $request->user_id;
        $chat_groups = ChatRoom::withCount(['chatRoomUsers' => function($q) use ($user_id) {
            $q->where('sender_id', $user_id);
        }])->where('group_type', 0);
        if($request->has('country_id') && !empty($request->country_id)) {
            $chat_groups = $chat_groups->where('country_id', $request->country_id); 
        }
        if($request->has('city_id') && !empty($request->city_id)) {
            $chat_groups = $chat_groups->where('city_id', $request->city_id); 
        }
        $groups = $chat_groups->get();
        return $this->returnSuccess(__(CHAT_GROUPS_FETCHED), $groups);
    }

    public function deleteChat(Request $request)
    {
        
    }
}
