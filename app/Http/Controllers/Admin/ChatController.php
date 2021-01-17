<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Chat;
use App\ChatRoom;
use App\User;
use Illuminate\Http\UploadedFile;
use Storage;
use Image;
use App\ChatRoomUser;

class ChatController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $chat_rooms = ChatRoom::withCount('ChatRoomUsers')->get();
        return view('pages.chat.index', compact('chat_rooms'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $users = User::where('is_admin', 0)->get();
        return view('pages.chat.add', compact('users'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $chat_room = new ChatRoom();
        $chat = new Chat();
        $data  = $request->all();
        
        $data['uuid'] = $chat->generateUuid(10);
        $data['is_group'] = $request->has('is_group') ? '1' : '0';

        $validator = $chat_room->validators($data);
        if ($validator->fails()) {
            return redirect()->back()
            ->withErrors($validator)
            ->withInput();
        }

        $chat_room->uuid = $data['uuid'];
        $chat_room->title = $request->title;
        $chat_room->is_group = $data['is_group'];

        $save = $chat_room->save();

        if ($save && array_key_exists('group_icon', $data) && $data['group_icon'] instanceof UploadedFile) {
            $id = $chat_room->id;

            $attachment = $data['group_icon'];
            $pathInfos = pathinfo($attachment->getClientOriginalName());

            if (!empty($pathInfos['extension'])) {
                $folder = $chat_room->folder . '/' . $id . '/icon//';
                $thumb_folder = $chat_room->folder . '/' . $id;

                if (!empty($folder)) {
                    $fileName  = (empty($pathInfos['filename']) ? time() : $pathInfos['filename']) . '_' . time() . '.' . $pathInfos['extension'];
                    $storeFile = $attachment->storeAs($folder, $fileName, $chat_room->fileSystem);

                    $thumb_image = Image::make($data['group_icon'])->resize(100, 100)->save($fileName);
                    \Storage::disk($chat_room->fileSystem)->put($thumb_folder.'/'.$fileName, $thumb_image, $chat_room->fileSystem);

                    if ($storeFile) {
                        $chat_room = $chat_room->find($id);

                        $chat_room->group_icon = $fileName;
                        $chat_room->group_icon_actual = $fileName;

                        $chat_room->save();
                    }
                }
            }
        }
        
        if($request->has('users') && !empty($request->users)) {
            foreach($request->users as $user) {
                $chat_room_users = new ChatRoomUser();
                $chat_room_users->chat_room_id = $chat_room->id;
                $chat_room_users->sender_id = $user;
                $chat_room_users->save();
            }
        }

        $request->session()->flash('alert-success', 'Chat Room successfully created');
        return redirect()->route('chats.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $chat_room = ChatRoom::with(['ChatRoomUsers.Users'])->find(decrypt($id));
        $chat_room->group_icon_actual = $chat_room->folder . '/' . $chat_room->id . '/icon/' . $chat_room->group_icon_actual;
        $chat_room->group_icon = $chat_room->folder . '/' . $chat_room->id . '/' . $chat_room->group_icon;
        return view('pages.chat.show', compact('chat_room'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $chat_room = ChatRoom::with(['ChatRoomUsers'])->find(decrypt($id));
        $chat_room_data = $chat_room->ChatRoomUsers->pluck('sender_id')->toArray();
        $users = User::where('is_admin', 0)->get();
        $chat_room->group_icon_actual = $chat_room->folder . '/' . $chat_room->id . '/icon/' . $chat_room->group_icon_actual;
        return view('pages.chat.edit', compact('chat_room', 'users', 'chat_room_data'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $chat_room = ChatRoom::with(['ChatRoomUsers'])->find(decrypt($id));
        $chat_room_data = $chat_room->ChatRoomUsers->pluck('id')->toArray();
        $prevIcon = $chat_room->group_icon_actual;
        $data  = $request->all();
        
        $data['is_group'] = $request->has('is_group') ? '1' : '0';

        $validator = $chat_room->validators($data);
        if ($validator->fails()) {
            return redirect()->back()
            ->withErrors($validator)
            ->withInput();
        }
        $chat_room->uuid = $request->uuid;
        $chat_room->title = $request->title;
        $chat_room->is_group = $data['is_group'];

        $save = $chat_room->save();
        $chat_users = $chat_room->ChatRoomUsers->pluck('sender_id')->toArray();
        if (array_key_exists('group_icon', $data) && $data['group_icon'] instanceof UploadedFile) {
            $id = $chat_room->id;

            $attachment = $data['group_icon'];
            if(!empty($prevIcon)) {
                Storage::delete($chat_room->fileSystem . '/'. $chat_room->folder .'/' .$id .'/'. $prevIcon);
                Storage::delete($chat_room->fileSystem . '/'. $chat_room->folder .'/' .$id .'/icon//'. $prevIcon);
            }
            $pathInfos = pathinfo($attachment->getClientOriginalName());

            if (!empty($pathInfos['extension'])) {
                $folder = $chat_room->folder . '/' . $id . '/icon//';
                $thumb_folder = $chat_room->folder . '/' . $id;

                if (!empty($folder)) {
                    $fileName  = (empty($pathInfos['filename']) ? time() : $pathInfos['filename']) . '_' . time() . '.' . $pathInfos['extension'];
                    $storeFile = $attachment->storeAs($folder, $fileName, $chat_room->fileSystem);

                    $thumb_image = Image::make($data['group_icon'])->resize(100, 100)->save($fileName);
                    \Storage::disk($chat_room->fileSystem)->put($thumb_folder.'/'.$fileName, $thumb_image, $chat_room->fileSystem);

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
        if($request->has('users') && !empty($request->users)) {
            foreach($request->users as $user) {
                $chat_room_users = new ChatRoomUser();
                if(in_array((int)$user, $chat_users)) {
                    $chat_room_users = ChatRoomUser::where('chat_room_id', $chat_room->id)->where('sender_id', $user)->first();
                }
                $chat_room_users->chat_room_id = $chat_room->id;
                $chat_room_users->sender_id = $user;
                $chat_room_users->save();
                $chat_users_list[] = $chat_room_users->id;
            }
            
            $deleted_users = array_diff($chat_room_data, $chat_users_list);
            if(!empty($deleted_users)) {
                ChatRoomUser::destroy($deleted_users);
            }
        }

        $request->session()->flash('alert-success', 'Chat Room successfully updated');
        return redirect()->route('chats.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        ChatRoom::where('id', decrypt($id))->delete();
        ChatRoomUser::where('chat_room_id', decrypt($id))->delete();
		\Session::flash('alert-success', 'Chat Room successfully removed');
		return redirect(url()->previous());
    }
}
