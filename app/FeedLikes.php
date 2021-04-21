<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Feed;
use App\User;
use Illuminate\Support\Facades\Validator;

class FeedLikes extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'feed_id'
    ];


    public function validator(array $data, $returnBoolsOnly = false)
    {
        $rules = [
            'feed_id' => array_merge(['integer', 'exists:feeds,id'], !empty($requiredFileds['feed_id']) ? $requiredFileds['feed_id'] : ['required']),
            'user_id' => array_merge(['integer', 'exists:users,id'], !empty($requiredFileds['user_id']) ? $requiredFileds['user_id'] : ['required']),
        ];

        $validator = Validator::make($data, $rules, [
            'user_id.required' => 'User id is mandatory.',
            'feed_id.required' => 'feed id is mandatory.'
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
