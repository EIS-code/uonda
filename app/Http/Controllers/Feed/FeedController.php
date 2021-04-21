<?php

namespace App\Http\Controllers\Feed;

use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use App\Feed;
use App\User;
use App\FeedLikes;

class FeedController extends BaseController
{
    public function getFeed(Request $request)
    {
        $model = new Feed();
        $data  = $request->all();

        $feeds = $model::orderBy('id', 'DESC')->get();
        $user = User::find((int)$data['user_id']);

        if (!empty($feeds)) {
            $status = 200;
            $success_res  = [
                'code' => $status,
                'msg' => __('Feeds get successfully!'),
                'data' => $feeds,
                'liked_feeds_arr' => $user->likedFeeds()->get()->pluck('id')
            ];
            return response()->json($success_res, 200);
        }

        return $this->returnNull();
    }

    //Function to save the like/dislikes the feeds
    public function setFeedLikes(Request $request) {
        $userModel = new User();
        $model = new FeedLikes();
        $data  = $request->all();

        if (empty($data['user_id']) || !is_numeric($data['user_id'])) {
            return $this->returnError(__('User id seems incorrect.'));
        }
        $userId = (int)$data['user_id'];
        $user = $userModel::find($userId);

        $fillableFields = $model->getFillable();
        $validator = $model->validator($data);

        if ($validator->fails()) {
            return $this->returnError($validator->errors()->first());
        }
        $feed_id = array(
            $request->feed_id
        );
        if($request->has('status') && $request->status == 0) {
            $msg = 'Feed disliked successfully!';
            $user->likedFeeds()->detach($feed_id);    
        } else {
            $msg = 'Feed liked successfully!';
            if(!$user->likedFeeds()->where('feed_id', $request->feed_id)->exists()) {
                $user->likedFeeds()->attach($feed_id);
            }
        }
        if(!empty($user->likedFeeds())) {
            return $this->returnSuccess(__($msg), $user->likedFeeds()->get());
        }
        return $this->returnNull();
    }
}
