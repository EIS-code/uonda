<?php

namespace App;

use Illuminate\Support\Facades\Validator;

class Country extends BaseModel
{
    public $timestamps = true;

    protected $fillable = [
        'name',
        'sort_name',
        'phone_code',
        'latitude',
        'longitude'
    ];

    protected $appends = ['encrypted_country_id'];

    public function validator(array $data, $id = false, $isUpdate = false)
    {
        return Validator::make($data, [
            'name'       => ['required', 'string', 'max:255'],
            'sort_name' => ['required', 'string', 'max:255'],
            'phone_code' => ['required', 'integer', 'max:255']
        ]);
    }

    public function states()
    {
        return $this->belongsToMany('App\State');
    }

    //get encrypted country id
    public function getEncryptedCountryIdAttribute()
    {
        return encrypt($this->id);
    }
}
