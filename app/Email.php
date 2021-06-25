<?php

namespace App;

use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

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

    const IS_SEND     = '1';
    const IS_NOT_SEND = '0';

    public static function validator(array $data)
    {
        return Validator::make($data, [
            'from'    => ['string', 'required', 'email'],
            'to.*'    => ['string', 'required', 'email'],
            'subject' => ['string', 'required', 'string'],
            'body'    => ['string', 'nullable'],
        ]);
    }

    //get encrypted email id
    public function getEncryptedEmailIdAttribute()
    {
        return encrypt($this->id);
    }

    public static function store(array $to, string $subject, string $body = NULL, array $cc = [], array $bcc = [], string $attachments = NULL, int $isSend = self::IS_SEND, string $exceptionInfo = NULL)
    {
        $insert = false;

        $from   = env('MAIL_FROM_ADDRESS', '');

        $data   = [
            'from'              => $from,
            'to'                => $to,
            'cc'                => $cc,
            'bcc'               => $bcc,
            'subject'           => $subject,
            'body'              => $body,
            'attachments'       => $attachments,
            'is_send'           => (string)$isSend,
            'exception_info'    => $exceptionInfo,
            'created_at'        => Carbon::now()
        ];

        $validator = self::validator($data);

        if ($validator->fails()) {
            return $validator->errors()->first();
        } else {
            $data['to']     = (!empty($to) && is_array($to)) ? implode(", ", $to) : NULL;

            $data['cc']     = (!empty($cc) && is_array($cc)) ? implode(", ", $cc) : NULL;

            $data['bcc']    = (!empty($bcc) && is_array($bcc)) ? implode(", ", $bcc) : NULL;

            $insert = Email::insert($data);
        }

        return $insert;
    }
}
