<?php

namespace App\Http\Controllers\Promotion;

use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use App\Promotions;

class PromotionController extends BaseController
{
    public function getPromotions(Request $request)
    {
        $model = new Promotions();
        $data  = $request->all();

        $promotions = $model::orderBy('id', 'DESC')->get();

        if (!empty($promotions)) {
            return $this->returnSuccess(__(PROMOTION_GET), $promotions);
        }

        return $this->returnNull();
    }
}
