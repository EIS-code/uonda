<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\School;
use App\Country;
use App\City;
use Illuminate\Support\Facades\Validator;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'referral_code', 'current_location', 'nation', 'gender', 'birthday', 'school_id', 'country_id', 'city_id'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function getTableName()
    {
        return with(new static)->getTable();
    }

    public function validator(array $data, $returnBoolsOnly = false)
    {
        $validator = Validator::make($data, [
            'name'             => ['required', 'string', 'max:255'],
            'password'         => ['required', 'string', 'min:6'],
            'email'            => ['required', 'email', 'unique:' . $this->getTableName()],
            'referral_code'    => ['nullable', 'string', 'max:255'],
            'current_location' => ['nullable', 'string'],
            'nation'           => ['nullable', 'string', 'max:255'],
            'gender'           => ['required', 'in:m,f'],
            'birthday'         => ['nullable'],
            'school_id'        => ['required', 'string', 'exists:' . School::getTableName() . ',id'],
            'country_id'       => ['required', 'integer', 'exists:' . Country::getTableName() . ',id'],
            'city_id'          => ['required', 'integer', 'exists:' . City::getTableName() . ',id']
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
