<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Group;
use App\UserGroup;
use Illuminate\Http\UploadedFile;
use Storage;

class GroupsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $groups = Group::get();
        return view('pages.groups.index', compact('groups'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('pages.groups.add');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $feed = new Group();
        $data  = $request->all();

        $validator = $feed->validator($data);
        if ($validator->fails()) {
            return redirect()->back()
            ->withErrors($validator)
            ->withInput();
        }
        
        $fillableFields = $feed->getFillable();

        foreach ($data as $field => $value) {
            if (in_array($field, $fillableFields)) {
                $feed->{$field} = $value;
            }
        }

        $feed->save();
        $request->session()->flash('alert-success', 'Group successfully created');
        return redirect()->route('groups.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $feed = Group::find(decrypt($id));
        
        if(!empty($feed)) {
            $feed['users'] = UserGroup::with("group", "user")->where("group_id",decrypt($id))->get();
        }
        return view('pages.groups.show', compact('feed'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $feed = Group::find(decrypt($id));
        return view('pages.groups.edit', compact('feed'));
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
        $feed = Group::find(decrypt($id));
        $prevAttachment = $feed->attachment;
        $data  = $request->all();

        $validator = $feed->validator($data);
        if ($validator->fails()) {
            return redirect()->back()
            ->withErrors($validator)
            ->withInput();
        }
        
        $fillableFields = $feed->getFillable();

        foreach ($data as $field => $value) {
            if (in_array($field, $fillableFields)) {
                $feed->{$field} = $value;
            }
        }

        $feed->save();
        $request->session()->flash('alert-success', 'Group successfully updated');
        return redirect()->route('groups.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        Group::where('id', decrypt($id))->delete();
		$request->session()->flash('success','Group deleted successfully');
		return redirect(url()->previous());
    }
}
