<?php

namespace App;

use Illuminate\Support\Facades\Validator;
use App\User;

class UserReferral extends BaseModel
{
    protected $fillable = [
        'user_id',
        'referral_user_id',
        'referral_code'
    ];

    public function validator(array $data, $id = false, $isUpdate = false)
    {
        return Validator::make($data, [
            'user_id'               => ['required', 'integer', 'exists:' . (new User())->getTableName() . ',id'],
            'referral_user_id'      => ['required', 'integer', 'exists:' . (new User())->getTableName() . ',id'],
            'referral_code'         => ['required', 'string']
        ]);
    }
    
    public function user() {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
