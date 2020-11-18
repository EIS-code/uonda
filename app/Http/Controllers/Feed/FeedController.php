<?php

namespace App\Http\Controllers\Feed;

use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use App\Feed;

class FeedController extends BaseController
{
    public function getFeed()
    {
        $model = new Feed();

        $feeds = $model::all();

        if (!empty($feeds)) {
            return $this->returnSuccess(__('Feeds get successfully!'), $feeds);
        }

        return $this->returnNull();
    }
}
