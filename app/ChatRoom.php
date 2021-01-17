<?php

namespace App;

use Illuminate\Support\Facades\Validator;
use App\ChatRoom;
use App\ChatRoomUser;
use Illuminate\Support\Facades\Storage;

class ChatRoom extends BaseModel
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'uuid', 'title', 'group_icon', 'group_icon_actual', 'is_group'
    ];

    protected $appends = ['encrypted_chat_id'];

    const IS_NOT_GROUP = '0';
    const IS_GROUP     = '1';

    public $isGroup = [
        self::IS_NOT_GROUP => 'Not',
        self::IS_GROUP     => 'Yes'
    ];

    public $fileSystem = 'public';
    public $folder     = 'user\\chat\\group';
    // public $folderIcon = 'user\\chat\\group\\icon';
    public $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];

    public function validators(array $data, $returnBoolsOnly = false)
    {
        $validator = Validator::make($data, [
            'uuid'              => ['required', 'string', 'max:255'],
            'title'             => ['nullable', 'string'],
            'group_icon'        => ['nullable', 'mimes:' . implode(",", $this->allowedExtensions)],
            'group_icon_actual' => ['nullable', 'mimes:' . implode(",", $this->allowedExtensions)],
            'is_group'          => ['in:' . implode(",", array_keys($this->isGroup))],
        ]);

        if ($returnBoolsOnly === true) {
            if ($validator->fails()) {
                \Session::flash('error', $validator->errors()->first());
            }

            return !$validator->fails();
        }

        return $validator;
    }

    // public function getGroupIconAttribute($value)
    // {
    //     if (empty($value)) {
    //         return $value;
    //     }

    //     $storageFolderName = (str_ireplace("\\", "/", $this->folder));
    //     return Storage::disk($this->fileSystem)->url($storageFolderName . '/' . $this->id . '/' . $value);
    // }

    // public function getGroupIconActualAttribute($value)
    // {
    //     if (empty($value)) {
    //         return $value;
    //     }

    //     $storageFolderName = (str_ireplace("\\", "/", $this->folder));
    //     return Storage::disk($this->fileSystem)->url($storageFolderName . '/' . $this->id . '/icon/' . $value);
    // }

    //get encrypted chat id
    public function getEncryptedChatIdAttribute()
    {
        return encrypt($this->id);
    }

    public function ChatRoomUsers()
    {
        return $this->hasMany('App\ChatRoomUser', 'chat_room_id', 'id');
    }
}
