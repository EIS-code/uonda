<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\School;

class SchoolController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $model  = new School();
        $feeds = $model::with('country', 'city')->orderBy($model::getTableName() . '.name', 'ASC')->get();
        return view('pages.schools.index', compact('feeds'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('pages.schools.add');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $feed = new School();
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
        $request->session()->flash('alert-success', 'School successfully created');
        return redirect()->route('schools.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $model  = new School();
        $feed = $model::with('country', 'city')->where("id",decrypt($id))->orderBy($model::getTableName() . '.name', 'ASC')->first();
        
        return view('pages.schools.show', compact('feed'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $feed = School::find(decrypt($id));
        return view('pages.schools.edit', compact('feed'));
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
        $feed = School::find(decrypt($id));
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
        $request->session()->flash('alert-success', 'School successfully updated');
        return redirect()->route('schools.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
