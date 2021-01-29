<?php

namespace App;

use Illuminate\Support\Facades\Validator;
use App\Country;

class State extends BaseModel
{
    protected $fillable = [
        'name',
        'country_id'
    ];

    protected $appends = ['encrypted_state_id'];

    public function validator(array $data, $id = false, $isUpdate = false)
    {
        return Validator::make($data, [
            'name'       => ['required', 'string', 'max:255'],
            'country_id' => ['required', 'integer']
        ]);
    }

    public function city()
    {
        return $this->belongsToMany('App\City');
    }

    public function country()
    {
        return $this->belongsTo('App\Country', 'country_id', 'id');
    }

    //get encrypted state id
    public function getEncryptedStateIdAttribute()
    {
        return encrypt($this->id);
    }
}
