<?php

namespace App;

use Illuminate\Support\Facades\Validator;

class EmailTemplate extends BaseModel
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'email_subject', 'email_body'
    ];

    protected $appends = ['encrypted_email_id'];

    const RESET_EMAIL_ID = '1';

    const WELCOME_EMAIL_ID = '2';

    public function validator(array $data, $returnBoolsOnly = false)
    {
        $validator = Validator::make($data, [
            'email_subject' => ['required', 'string'],
            'email_body'    => ['required', 'string']
        ]);

        if ($returnBoolsOnly === true) {
            if ($validator->fails()) {
                \Session::flash('error', $validator->errors()->first());
            }

            return !$validator->fails();
        }

        return $validator;
    }

    // Get encrypted email template id
    public function getEncryptedEmailIdAttribute()
    {
        return encrypt($this->id);
    }
}
