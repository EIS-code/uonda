<?php

namespace App;

use Illuminate\Support\Facades\Validator;
use App\ChatRoom;
use App\ChatRoomUser;

class ChatRoom extends BaseModel
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'uuid', 'title', 'is_group'
    ];

    const IS_NOT_GROUP = '0';
    const IS_GROUP     = '1';

    public $isGroup = [
        self::IS_NOT_GROUP => 'Not',
        self::IS_GROUP     => 'Yes'
    ];

    public function validators(array $data, $returnBoolsOnly = false)
    {
        $validator = Validator::make($data, [
            'uuid'     => ['required', 'string', 'max:255'],
            'title'    => ['nullable', 'integer', 'exists:' . (new ChatRoom())->getTableName() . ',id'],
            'is_group' => ['in:' . implode(",", array_keys($this->isGroup))],
        ]);

        if ($returnBoolsOnly === true) {
            if ($validator->fails()) {
                \Session::flash('error', $validator->errors()->first());
            }

            return !$validator->fails();
        }

        return $validator;
    }
}
