<?php

namespace App;

use Illuminate\Support\Facades\Validator;

class Constant extends BaseModel
{
    protected $fillable = [
        'key',
        'value',
        'is_removed'
    ];

    protected $appends = ['encrypted_constant_id'];

    public function validator(array $data)
    {
        return Validator::make($data, [
            'key'        => ['required', 'string'],
            'value'      => ['required', 'string'],
            'is_removed' => ['integer', 'in:0,1']
        ]);
    }

    //get encrypted constant id
    public function getEncryptedConstantIdAttribute()
    {
        return encrypt($this->id);
    }
}
