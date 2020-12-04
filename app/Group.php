<?php

namespace App;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class Group extends BaseModel
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title', 'description'
    ];

    
    /**
     * The attributes that should be appends to model object.
     *
     * @var array
     */
    protected $appends = ['encrypted_group_id'];

    public function __construct(array $attributes = array())
    {
        parent::__construct($attributes);

        $this->makeVisible('created_at');
    }

    public function validator(array $data, $returnBoolsOnly = false)
    {
        $validator = Validator::make($data, [
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['required', 'string']
        ]);

        if ($returnBoolsOnly === true) {
            if ($validator->fails()) {
                \Session::flash('error', $validator->errors()->first());
            }

            return !$validator->fails();
        }

        return $validator;
    }

    //get encrypted feed id
    public function getEncryptedGroupIdAttribute()
    {
        return encrypt($this->id);
    }
}
