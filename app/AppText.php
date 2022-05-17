<?php

namespace App;

use Illuminate\Support\Facades\Validator;

class AppText extends BaseModel
{
    protected $fillable = [
        'key',
        'english_text',
        'show_text',
        'type',
    ];

    const NOTIFICATION = 0;
    const API_RESPONSE = 1;
    
    public function validator(array $data)
    {
        $validator = Validator::make($data, [
            'key'               => ['required', 'string', 'max:255'],
            'english_text'      => ['required', 'string', 'max:255'],
            'show_text'         => ['required', 'string', 'max:255'],
            'type'              => ['in:0,1'],
        ]);

        return $validator;
    }
}
