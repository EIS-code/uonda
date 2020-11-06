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
        'name', 'email', 'password', 'referral_code', 'current_location', 'nation', 'gender', 'birthday', 'school_id', 'country_id', 'city_id',
        'current_status', 'company', 'job_position', 'university', 'field_of_study'
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

    public function validator(array $data, $requiredFileds = [], $extraFields = [], $returnBoolsOnly = false)
    {
        $validator = Validator::make($data, array_merge([
            'name'             => array_merge(['string', 'max:255'], !empty($requiredFileds['name']) ? $requiredFileds['name'] : ['required']),
            'password'         => array_merge(['string', 'min:6'], !empty($requiredFileds['password']) ? $requiredFileds['password'] : ['required']),
            'email'            => array_merge(['email', 'unique:' . $this->getTableName()], !empty($requiredFileds['email']) ? $requiredFileds['email'] : ['required']),
            'referral_code'    => array_merge(['string', 'max:255'], !empty($requiredFileds['referral_code']) ? $requiredFileds['referral_code'] : ['nullable']),
            'current_location' => array_merge(['string'], !empty($requiredFileds['current_location']) ? $requiredFileds['current_location'] : ['nullable']),
            'nation'           => array_merge(['string', 'max:255'], !empty($requiredFileds['nation']) ? $requiredFileds['nation'] : ['nullable']),
            'gender'           => array_merge(['in:m,f'], !empty($requiredFileds['gender']) ? $requiredFileds['gender'] : ['nullable']),
            'birthday'         => array_merge([], !empty($requiredFileds['string']) ? $requiredFileds['string'] : ['nullable']),
            'school_id'        => array_merge(['integer', 'exists:' . School::getTableName() . ',id'], !empty($requiredFileds['school_id']) ? $requiredFileds['school_id'] : ['nullable']),
            'country_id'       => array_merge(['integer', 'exists:' . Country::getTableName() . ',id'], !empty($requiredFileds['country_id']) ? $requiredFileds['country_id'] : ['nullable']),
            'city_id'          => array_merge(['integer', 'exists:' . City::getTableName() . ',id'], !empty($requiredFileds['city_id']) ? $requiredFileds['city_id'] : ['nullable']),
            'current_status'   => array_merge(['in:0,1,2,3'], !empty($requiredFileds['current_status']) ? $requiredFileds['current_status'] : ['nullable']),
            'company'          => array_merge(['string', 'max:255'], !empty($requiredFileds['company']) ? $requiredFileds['company'] : ['nullable']),
            'job_position'     => array_merge(['string', 'max:255'], !empty($requiredFileds['job_position']) ? $requiredFileds['job_position'] : ['nullable']),
            'university'       => array_merge(['string', 'max:255'], !empty($requiredFileds['university']) ? $requiredFileds['university'] : ['nullable']),
            'field_of_study'   => array_merge(['string', 'max:255'], !empty($requiredFileds['field_of_study']) ? $requiredFileds['field_of_study'] : ['nullable'])
        ], $extraFields));

        if ($returnBoolsOnly === true) {
            if ($validator->fails()) {
                \Session::flash('error', $validator->errors()->first());
            }

            return !$validator->fails();
        }

        return $validator;
    }
}
