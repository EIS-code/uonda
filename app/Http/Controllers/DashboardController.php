<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\School;
use App\Feed;
use App\PromoCode;
use App\SubscriptionPlan;
use App\ChatRoom;

class DashboardController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $data = array();
        $data['users_count'] = User::where('is_admin', 0)->count();
        $data['feeds_count'] = Feed::count();
        $data['codes_count'] = PromoCode::count();
        $data['plans_count'] = SubscriptionPlan::count();
        $data['groups_count'] = ChatRoom::count();
        $data['schools_count'] = School::count();
        return view('pages/dashboard', compact('data'));
    }
}
