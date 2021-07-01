<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Notification;
use Illuminate\Support\Facades\Schema;

class NotificationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $columns=Schema::getColumnListing('notifications');
        $orderBy = ($request->input('sortBy') && in_array($request->input('sortBy'), $columns))?$request->input('sortBy'):'id';
        $orderOrder = ($request->input('sortOrder') && ($request->input('sortOrder') == 'asc' || $request->input('sortOrder') == 'desc'))?$request->input('sortOrder'):'asc';
        $limit = env('PAGINATION_PER_PAGE_RECORDS') ? env('PAGINATION_PER_PAGE_RECORDS') : 200;
        $search = ($request->input('search') && $request->input('search') != '')?$request->input('search'):'';
        $notifications = Notification::where('user_id', auth()->user()->id)->where(function($query) use ($search){
            if($search) {
                $searchColumn = ['title'];
                foreach ($searchColumn as $singleSearchColumn) {
                    $query->orWhere($singleSearchColumn, "LIKE", '%' . $search . '%');
                }
            }
        });
        $notifications = $notifications->orderBy($orderBy, $orderOrder)->paginate($limit);
        
        return view('pages.notification.index', compact('notifications'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        
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

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        
    }
    
    public function read($id) {
        
        $notification = Notification::find($id);
        $notification->where('id', $id)->update(['is_read' => Notification::IS_READ]);
        \Session::flash('alert-success','Notification read successfully');
        return redirect(url()->previous());
    }
}
