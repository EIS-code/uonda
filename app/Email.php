<?php

namespace App;

use Illuminate\Support\Facades\Validator;

class Email extends BaseModel
{
    protected $fillable = [
        'from',
        'to',
        'cc',
        'bcc',
        'subject',
        'body',
        'is_send',
        'exception_info',
        'created_at'
    ];

    protected $appends = ['encrypted_email_id'];

    public static function validator(array $data)
    {
        return Validator::make($data, [
            'to.*'    => ['required', 'email'],
            'subject' => ['required', 'string'],
            'body'    => ['required'],
        ]);
    }

    //get encrypted email id
    public function getEncryptedEmailIdAttribute()
    {
        return encrypt($this->id);
    }
}
