<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use App\User;
use App\School;
use App\Country;
use App\City;
use App\UserBlockProfile;
use App\Notification;
use App\Jobs\UserRejectNotification;
use App\Jobs\UserAcceptNotification;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $type)
    {
        $columns=Schema::getColumnListing('users');
        $orderBy = ($request->input('sortBy') && in_array($request->input('sortBy'), $columns))?$request->input('sortBy'):'id';
        $orderOrder = ($request->input('sortOrder') && ($request->input('sortOrder') == 'asc' || $request->input('sortOrder') == 'desc'))?$request->input('sortOrder'):'asc';
        $limit = env('PAGINATION_PER_PAGE_RECORDS') ? env('PAGINATION_PER_PAGE_RECORDS') : 200;
        $search = ($request->input('search') && $request->input('search') != '')?$request->input('search'):'';
        $users = User::where('is_admin', 0);
        $users->where(function($query) use ($search){
            if($search) {
                $searchColumn = ['name', 'email'];
                foreach ($searchColumn as $singleSearchColumn) {
                    $query->orWhere($singleSearchColumn, "LIKE", '%' . $search . '%');
                }
            }
        });
        switch($type) {
            case 'pending':
                $users->where('is_accepted', 0);
                break;
            case 'rejected':
                $users->where('is_accepted', 2);
                break;
            case 'accepted':
                $users->where('is_accepted', 1);
                break;
            default :
                $users->where('is_accepted', 1);
        }
        $users = $users->orderBy($orderBy, $orderOrder)->paginate($limit);
        return view('pages.users.index', compact('users', 'type'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = User::with(['userDocuments', 'referralUsers'])->find(decrypt($id));
        $data = array();
        if(!empty($user->school_id)) {
            $data['school_name'] = School::select('name')->where('id', $user->school_id)->first();
        }
        if(!empty($user->country_id)) {
            $data['country_name'] = Country::select('name')->where('id', $user->country_id)->first();
        }
        if(!empty($user->city_id)) {
            $data['city_name'] = City::select('name')->where('id', $user->city_id)->first();
        }
        return view('pages.users.show', compact('user', 'data'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $user = User::find(decrypt($id));
        if(!empty($user)) {
            $data  = $request->all();
        
            if($request->has('description')) {
                $user->reason_for_rejection = $request->description;
                $user->is_accepted = 2;
            }
            if($request->has('ís_accepted')) {
                $user->reason_for_rejection = NULL;
                $user->is_accepted = 1;
            }
            if($request->has('user_status')) {
                $user->is_enable = $request->user_status;
            }

            $user->save();

            $user->refresh();

            // For rejection.
            if ($user->is_accepted == User::IS_REJECTED) {
                $dataPayload['data']                = json_encode(['reason_for_rejection' => !empty($user->reason_for_rejection) ? $user->reason_for_rejection : NULL]);

                $dataPayload['notification_type']   = Notification::NOTIFICATION_REJECT_USER;

                UserRejectNotification::dispatch($user->id, $dataPayload)->delay(now()->addSeconds(2));
            } elseif ($user->is_accepted == User::IS_ACCEPTED) {
                $dataPayload['data']                = json_encode([]);

                $dataPayload['notification_type']   = Notification::NOTIFICATION_ACCEPT_USER;

                UserAcceptNotification::dispatch($user->id, $dataPayload)->delay(now()->addSeconds(2));
            }

            $request->session()->flash('alert-success', 'User successfully updated');

            return response()->json(['success' => true, 'status' => 200], 200);
        } else {
            return response()->json(['success' => false, 'status' => 400], 400);
        }
        
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        User::where('id', decrypt($id))->delete();
		$request->session()->flash('success','User deleted successfully');
		return redirect(url()->previous());
    }

    //
    public function showBlockedUser(Request $request) {
        $block_profiles = UserBlockProfile::with(['user', 'blockedUser'])->get();
        return view('pages.users.blocked-user-listing', compact('block_profiles'));
    }
}
