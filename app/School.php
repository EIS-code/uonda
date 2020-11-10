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

    public function validator(array $data, $returnBoolsOnly = false)
    {
        $validator = Validator::make($data, [
            'name'       => ['required', 'string', 'unique:' . $this->getTableName(), 'max:255'],
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
}
