<?php

namespace App;

use Illuminate\Support\Facades\Validator;
use App\State;

class City extends BaseModel
{
    protected $fillable = [
        'name',
        'state_id'
    ];

    protected $appends = ['encrypted_city_id'];

    public function validator(array $data, $returnBoolsOnly = false)
    {
        $validator = Validator::make($data, [
            'name'     => ['required', 'string', 'max:255'],
            'state_id' => ['required', 'integer', 'exists:' . State::getTableName() . ',id']
        ]);

        if ($returnBoolsOnly === true) {
            if ($validator->fails()) {
                \Session::flash('error', $validator->errors()->first());
            }

            return !$validator->fails();
        }

        return $validator;
    }

    public function stats()
    {
        return $this->hasMany('App\Province', 'id', 'state_id');
    }

    public function state()
    {
        return $this->belongsTo('App\State', 'state_id', 'id');
    }

    //get encrypted city id
    public function getEncryptedCityIdAttribute()
    {
        return encrypt($this->id);
    }

    public function Users()
    {
        return $this->hasMany('App\User', 'city_id', 'id');
    }

    public function user()
    {
        return $this->hasOne('App\User', 'city_id', 'id');
    }
}
