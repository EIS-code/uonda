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

        // Fix 10 limit as this is deprecated and stuck if many records.
        $feeds = $model::orderBy('id', 'DESC')->limit(5)->get();
        $user = User::find((int)$data['user_id']);

        if (!empty($feeds) && !$feeds->isEmpty() && !empty($user)) {
            $status = 200;
            $success_res  = [
                'code' => $status,
                'msg' => __(FEEDS_GET),
                'data' => $feeds,
                'liked_feeds_arr' => $user->likedFeeds()->get()->pluck('id')
            ];
            return response()->json($success_res, 200);
        }

        return $this->returnNull();
    }

    public function getFeedPaginate(Request $request)
    {
        $model      = new Feed();
        $userId     = (int)$request->get('user_id', null);
        $pageNumber = (int)$request->get('page_number', 1);
        $pageNumber = (!empty($pageNumber) && is_numeric($pageNumber) && $pageNumber > 0) ? $pageNumber : 1;
        $perPage    = (int)$request->get('per_page', $model::PAGINATE_RECORDS);
        $perPage    = (!empty($perPage) && is_numeric($perPage) && $perPage > 0) ? $perPage : $model::PAGINATE_RECORDS;

        $feeds = $model::orderBy('id', 'DESC')->paginate($perPage, ['*'], 'page', $pageNumber);
        $user  = User::find($userId);

        if (!empty($feeds) && !$feeds->isEmpty() && !empty($user)) {
            $feedIds = $feeds->pluck('id')->toArray();
            $status  = 200;

            $successRes  = [
                'code' => $status,
                'msg' => __(FEEDS_GET),
                'data' => $feeds,
                'liked_feeds_arr' => $user->likedFeedsById($feedIds)->get()->pluck('id')
            ];

            return response()->json($successRes, 200);
        }

        return $this->returnNull();
    }

    //Function to save the like/dislikes the feeds
    public function setFeedLikes(Request $request) {
        $userModel = new User();
        $model = new FeedLikes();
        $data  = $request->all();

        if (empty($data['user_id']) || !is_numeric($data['user_id'])) {
            return $this->returnError(__(INCORRECT_USERID));
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

        if (!empty($user)) {
            if($request->has('status') && $request->status == 0) {
                $msg = FEED_DISLIKED;
                $user->likedFeeds()->detach($feed_id);    
            } else {
                $msg = 'Feed liked successfully!';
                if(!$user->likedFeeds()->where('feed_id', $request->feed_id)->exists()) {
                    $user->likedFeeds()->attach($feed_id);
                }
            }
            if(!empty($user->likedFeeds())) {
                return $this->returnSuccess(__($msg), $user->likedFeeds()->get()->pluck('id'));
            }
        }

        return $this->returnNull();
    }
}
