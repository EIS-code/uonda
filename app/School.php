<?php

namespace App;

use Illuminate\Support\Facades\Validator;
use App\City;
use App\Country;

class School extends BaseModel
{
    protected $fillable = [
        'name', 'city_id', 'country_id'
    ];

    public function validator(array $data, $id = false, $returnBoolsOnly = false)
    {
        $name = ['unique:' . $this->getTableName()];

        if (!empty($id)) {
            $name = ['unique:' . $this->getTableName() . ',name,' . $id];
        }

        $validator = Validator::make($data, [
            'name'       => array_merge(['required', 'string', 'max:255'], $name),
            'city_id'    => ['required', 'integer', 'exists:' . City::getTableName() . ',id'],
            'country_id' => ['required', 'integer', 'exists:' . Country::getTableName() . ',id']
        ]);

        if ($returnBoolsOnly === true) {
            if ($validator->fails()) {
                \Session::flash('error', $validator->errors()->first());
            }

            return !$validator->fails();
        }

        return $validator;
    }

    public function country()
    {
        return $this->hasOne('App\Country', 'id', 'country_id');
    }

    public function city()
    {
        return $this->hasOne('App\City', 'id', 'city_id');
    }

    public function users()
    {
        return $this->hasMany('App\User', 'school_id', 'id');
    }
}
