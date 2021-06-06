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
        'uuid', 'title', 'description', 'group_icon', 'group_icon_actual', 'is_group', 'created_by_admin', 'created_by' , 'group_type', 'city_id', 'country_id'
    ];

    protected $appends = ['encrypted_chat_id', 'country_name', 'city_name'];

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
            'description'       => ['nullable', 'string'],
            'group_icon'        => ['nullable', 'mimes:' . implode(",", $this->allowedExtensions)],
            'group_icon_actual' => ['nullable', 'mimes:' . implode(",", $this->allowedExtensions)],
            'is_group'          => ['in:' . implode(",", array_keys($this->isGroup))],
            'created_by_admin'  => ['nullable'],
            'created_by'        => ['nullable', 'integer', 'exists:' . (new User())->getTableName() . ',id']
        ]);

        if ($returnBoolsOnly === true) {
            if ($validator->fails()) {
                \Session::flash('error', $validator->errors()->first());
            }

            return !$validator->fails();
        }

        return $validator;
    }

    public function getGroupIconActualAttribute($value)
    {
        if (empty($value)) {
            return $value;
        }

        $storageFolderName = (str_ireplace("\\", "/", $this->folder));
        return Storage::disk($this->fileSystem)->url($storageFolderName . '/' . $this->id . '/' . $value);
    }

    public function getGroupIconAttribute($value)
    {
        if (empty($value)) {
            return $value;
        }

        $storageFolderName = (str_ireplace("\\", "/", $this->folder));
        return Storage::disk($this->fileSystem)->url($storageFolderName . '/' . $this->id . '/icon/' . $value);
    }

    //get encrypted chat id
    public function getEncryptedChatIdAttribute()
    {
        return encrypt($this->id);
    }

    public function chatRoomUsers()
    {
        return $this->hasMany('App\ChatRoomUser', 'chat_room_id', 'id');
    }

    public function createdBy()
    {
        return $this->belongsTo('App\User', 'created_by', 'id');
    }

    public function totalGroupParticipants(int $id)
    {
        return $this->find($id)->chatRoomUsers->count();
    }

    public function getCountryNameAttribute($value)
    {
        if(!empty($this->country_id)) {
            return Country::where('id', $this->country_id)->pluck('name')->first();
        }
        return NULL;
    }

    public function getCityNameAttribute($value)
    {
        if(!empty($this->city_id)) {
            return City::where('id', $this->city_id)->pluck('name')->first();
        }
        return NULL;
    }
}
