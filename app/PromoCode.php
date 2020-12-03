<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class PromoCode extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'promo_code', 'amount', 'percentage', 'status'
    ];

    /**
     * The attributes that should be appends to model object.
     *
     * @var array
     */
    protected $appends = ['encrypted_code_id'];

    public function __construct(array $attributes = array())
    {
        parent::__construct($attributes);

        $this->makeVisible('created_at');
    }

    public function validator(array $data, $returnBoolsOnly = false)
    {
        $validator = Validator::make($data, [
            'promo_code'       => ['required', 'string', 'max:255'],
            'amount'   => ['required_without_all:percentage'],
            'percentage'  => ['required_without_all:amount']
        ]);

        if ($returnBoolsOnly === true) {
            if ($validator->fails()) {
                \Session::flash('error', $validator->errors()->first());
            }

            return !$validator->fails();
        }

        return $validator;
    }

    //get encrypted promo code id
    public function getEncryptedCodeIdAttribute()
    {
        return encrypt($this->id);
    }
}
