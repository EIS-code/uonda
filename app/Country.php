<?php

namespace App;

use Illuminate\Support\Facades\Validator;

class Country extends BaseModel
{
    public $timestamps = true;

    protected $fillable = [
        'name',
        'short_name',
        'phone_code'
    ];

    public function validator(array $data, $id = false, $isUpdate = false)
    {
        return Validator::make($data, [
            'name'       => ['required', 'string', 'max:255'],
            'short_name' => ['required', 'string', 'max:255'],
            'phone_code' => ['required', 'integer', 'max:255']
        ]);
    }

    public function states()
    {
        return $this->belongsToMany('App\State');
    }
}
