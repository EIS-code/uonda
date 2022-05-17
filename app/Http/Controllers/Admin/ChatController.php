<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Chat;
use App\ChatRoom;
use App\UserBlockProfile;
use App\User;
use Illuminate\Http\UploadedFile;
use Storage;
use Image;
use Auth;
use App\ChatRoomUser;
use App\Country;
use App\City;

class ChatController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $chat_rooms = ChatRoom::with('createdBy')->withCount('ChatRoomUsers')->get();
        return view('pages.chat.index', compact('chat_rooms'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $countries = Country::get();
        $blockedUser = UserBlockProfile::where('is_block' , '1')->get()->pluck('user_id')->toArray();
        
        if(!empty($blockedUser)) {
            $users = User::where('is_admin', 0)->whereNotIn('id' , $blockedUser)->get();
        } else{ 
            $users = User::where('is_admin', 0)->get();
        }
        
        return view('pages.chat.add', compact('users', 'countries'));
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
        $data['created_by_admin'] = 1;
        $data['created_by'] = Auth::id();

        $validator = $chat_room->validators($data);
        if ($validator->fails()) {
            return redirect()->back()
            ->withErrors($validator)
            ->withInput();
        }

        $chat_room->uuid = $data['uuid'];
        $chat_room->title = $request->title;
        $chat_room->description = !empty($request->description) ? $request->description : NULL;
        $chat_room->is_group = $data['is_group'];
        $chat_room->created_by_admin = $data['created_by_admin'];
        $chat_room->created_by = $data['created_by'];
        $chat_room->group_type = $data['group_type'];
        $chat_room->country_id = !empty($data['country_id']) ? $data['country_id'] : NULL;
        $chat_room->city_id = !empty($data['city_id']) ? $data['city_id'] : NULL;

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
                    $fileName  = removeSpaces($fileName);
                    $storeFile = $attachment->storeAs($folder, $fileName, $chat_room->fileSystem);

                    /* $thumb_image = Image::make($data['group_icon'])->resize(100, 100)->save($fileName);
                    \Storage::disk($chat_room->fileSystem)->put($thumb_folder.'/'.$fileName, $thumb_image, $chat_room->fileSystem); */

                    $thumb_image = Image::make($data['group_icon'])->resize(100, 100);
                    \Storage::disk($chat_room->fileSystem)->put($thumb_folder.'/'.$fileName, $thumb_image->encode(), $chat_room->fileSystem);

                    
                        $chat_room = $chat_room->find($id);

                        $chat_room->group_icon = $fileName;
                        $chat_room->group_icon_actual = $fileName;

                       $chat_room->save();
                    
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
        $chat_room = ChatRoom::with(['ChatRoomUsers.Users', 'createdBy'])->find(decrypt($id));
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
        $countries = Country::get();
        $chat_room = ChatRoom::with(['ChatRoomUsers'])->find(decrypt($id));
        $chat_room_data = $chat_room->ChatRoomUsers->pluck('sender_id')->toArray();
        $blockedUser = UserBlockProfile::where('is_block' , '1')->get()->pluck('user_id')->toArray();
        
        if(!empty($blockedUser)) {
            $users = User::where('is_admin', 0)->whereNotIn('id' , $blockedUser)->get();
        } else{ 
            $users = User::where('is_admin', 0)->get();
        }
        $cities = array();
        if(!empty($chat_room->country_id)) {
            $country_id = $chat_room->country_id;
            $cities = City::with(['state' => function($q) use ($country_id) {
                $q->where('country_id', $country_id);
            }])
            ->whereHas('state', function($q) use ($country_id) {
                $q->where('country_id', $country_id);
            })->get();
        }
        return view('pages.chat.edit', compact('chat_room', 'users', 'chat_room_data', 'countries', 'cities'));
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
        $chat_room->description = !empty($request->description) ? $request->description : NULL;
        $chat_room->is_group = $data['is_group'];
        $chat_room->group_type = $data['group_type'];
        $chat_room->country_id = !empty($data['country_id']) ? $data['country_id'] : NULL;
        $chat_room->city_id = !empty($data['city_id']) ? $data['city_id'] : NULL;

        $save = $chat_room->save();
        $chat_users = $chat_room->ChatRoomUsers->pluck('sender_id')->toArray();
        if (array_key_exists('group_icon', $data) && $data['group_icon'] instanceof UploadedFile) {
            $id = $chat_room->id;

            $attachment = $data['group_icon'];
            if(!empty($prevIcon)) {
                $array = explode('/', $prevIcon);
                $key = array_key_last($array);
                $image = $array[$key];
                Storage::delete($chat_room->fileSystem . '/'. $chat_room->folder .'/' .$id .'/'. $image);
                Storage::delete($chat_room->fileSystem . '/'. $chat_room->folder .'/' .$id .'/icon//'. $image);
            }
            $pathInfos = pathinfo($attachment->getClientOriginalName());

            if (!empty($pathInfos['extension'])) {
                $folder = $chat_room->folder . '/' . $id . '/icon//';
                $thumb_folder = $chat_room->folder . '/' . $id;

                if (!empty($folder)) {
                    $fileName  = (empty($pathInfos['filename']) ? time() : $pathInfos['filename']) . '_' . time() . '.' . $pathInfos['extension'];
                    $fileName  = removeSpaces($fileName);
                    $storeFile = $attachment->storeAs($folder, $fileName, $chat_room->fileSystem);

                    /* $thumb_image = Image::make($data['group_icon'])->resize(100, 100)->save($fileName);
                    \Storage::disk($chat_room->fileSystem)->put($thumb_folder.'/'.$fileName, $thumb_image, $chat_room->fileSystem); */

                    $thumb_image = Image::make($data['group_icon'])->resize(100, 100);
                    \Storage::disk($chat_room->fileSystem)->put($thumb_folder.'/'.$fileName, $thumb_image->encode(), $chat_room->fileSystem);

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
