<?php

namespace App;

use Illuminate\Support\Facades\Validator;

class School extends BaseModel
{
    protected $fillable = [
        'name'
    ];

    public function validator(array $data, $returnBoolsOnly = false)
    {
        $validator = Validator::make($data, [
            'name'     => ['required', 'string', 'max:255']
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
