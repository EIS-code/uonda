<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\School;
use App\Country;
use App\State;
use App\City;

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
        $schools = $model::with('country', 'city', 'state')->orderBy($model::getTableName() . '.name', 'ASC')->get();
        return view('pages.schools.index', compact('schools'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $countries = Country::get();
        return view('pages.schools.add', compact('countries'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $school = new School();
        $data  = $request->all();

        $validator = $school->validator($data);
        if ($validator->fails()) {
            return redirect()->back()
            ->withErrors($validator)
            ->withInput();
        }

        $school->name = $request->name;
        $school->description = !empty($request->description) ? $request->description : '';
        $school->country_id = $request->country_id;
        $school->state_id = $request->state_id;
        $school->city_id = $request->city_id;
        $school->is_active = array_key_exists('is_active', $data) ? 1 : 0 ;
        $school->save();
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
        $school  = new School();
        $school = $school::with('country', 'city', 'state')->where("id",decrypt($id))->orderBy($school::getTableName() . '.name', 'ASC')->first();
        
        return view('pages.schools.show', compact('school'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $school = School::with(['state', 'country', 'city'])->find(decrypt($id));
        $countries = Country::all();
        $states = State::where('country_id', $school->country_id)->get();
        $cities = City::where('state_id', $school->state_id)->get();
        return view('pages.schools.edit', compact('school', 'countries', 'states', 'cities'));
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
        $school = School::find(decrypt($id));
        $data  = $request->all();

        $validator = $school->validator($data, decrypt($id));
        if ($validator->fails()) {
            return redirect()->back()
            ->withErrors($validator)
            ->withInput();
        }
        
        $school->name = $request->name;
        $school->description = !empty($request->description) ? $request->description : '';
        $school->country_id = $request->country_id;
        $school->state_id = $request->state_id;
        $school->city_id = $request->city_id;
        $school->is_active = array_key_exists('is_active', $data) ? 1 : 0 ;

        $school->save();
        $request->session()->flash('alert-success', 'School successfully updated');
        return redirect()->route('schools.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        School::where('id', decrypt($id))->delete();
		$request->session()->flash('success','School deleted successfully');
		return redirect(url()->previous());
    }

    //function to get the states details from the country
    public function getStateDetails(Request $request, $id) {
        $states = State::where('country_id', $id)->get();
        return response()->json(['data' => $states, 'status' => 200], 200);
    }

    //function to get the cities details from the state
    public function getCitiesDetails(Request $request, $id) {
        $cities = City::where('state_id', $id)->get();
        return response()->json(['data' => $cities, 'status' => 200], 200);
    }

    //Function to get the cities based on countries
    public function getCitiesFromCountry(Request $request, $id) {
        $country_id = $id;
        $cities = City::with(['state' => function($q) use ($country_id) {
                        $q->where('country_id', $country_id);
                    }])
                    ->whereHas('state', function($q) use ($country_id) {
                        $q->where('country_id', $country_id);
                    })->get();
        return response()->json(['data' => $cities, 'status' => 200], 200);
    }
}
