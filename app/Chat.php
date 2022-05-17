<?php

namespace App;

use Illuminate\Support\Facades\Validator;
use App\ChatRoom;
use App\ChatRoomUser;
use Illuminate\Database\Eloquent\SoftDeletes;

class Chat extends BaseModel
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'message', 'is_attachment', 'chat_room_id', 'chat_room_user_id'
    ];

    protected $casts = [
        'message'           => 'string',
        'is_attachment'     => 'boolean',
        'chat_room_id'      => 'integer',
        'chat_room_user_id' => 'integer'
    ];

    const NO_ATTACHMENT = '0';
    const IS_ATTACHMENT = '1';
    public $isAttachments = [
        self::NO_ATTACHMENT => 'Nope',
        self::IS_ATTACHMENT => 'Yes'
    ];

    public function validators(array $data, $returnBoolsOnly = false)
    {
        $validator = Validator::make($data, [
            'message'           => ['nullable', 'string', 'max:255'],
            'is_attachment'     => ['in:' . implode(",", array_keys($this->isAttachments))],
            'chat_room_id'      => ['required', 'integer', 'exists:' . (new ChatRoom())->getTableName() . ',id'],
            'chat_room_user_id' => ['required', 'integer', 'exists:' . (new ChatRoomUser())->getTableName() . ',id'],
        ]);

        if ($returnBoolsOnly === true) {
            if ($validator->fails()) {
                \Session::flash('error', $validator->errors()->first());
            }

            return !$validator->fails();
        }

        return $validator;
    }

    public function generateUuid($length)
    {
        /*$_sym = 'abcdefghijklmnopqrstuvwxyz1234567890';
        $str  = '';

        for($i = 0; $i < $count; $i++) {
            $str += $_sym[parseInt(Math.random() * (_sym.length))];
        }

        return $str;*/
        /*return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            // 32 bits for "time_low"
            mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),

            // 16 bits for "time_mid"
            mt_rand( 0, 0xffff ),

            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 4
            mt_rand( 0, 0x0fff ) | 0x4000,

            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            mt_rand( 0, 0x3fff ) | 0x8000,

            // 48 bits for "node"
            mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
        );*/

        $token        = "";
        $codeAlphabet = "abcdefghijklmnopqrstuvwxyz";
        $codeAlphabet .= "1234567890";
        $max = strlen($codeAlphabet);

        for ($i=0; $i < $length; $i++) {
            $token .= $codeAlphabet[rand(0, $max-1)];
        }

        return $token;
    }
}
