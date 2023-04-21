<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Country;
use Illuminate\Support\Facades\Schema;

class CountryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $columns=Schema::getColumnListing('countries');
        $orderBy = ($request->input('sortBy') && in_array($request->input('sortBy'), $columns))?$request->input('sortBy'):'id';
        $orderOrder = ($request->input('sortOrder') && ($request->input('sortOrder') == 'asc' || $request->input('sortOrder') == 'desc'))?$request->input('sortOrder'):'asc';
        $limit = env('PAGINATION_PER_PAGE_RECORDS') ? env('PAGINATION_PER_PAGE_RECORDS') : 200;
        $search = ($request->input('search') && $request->input('search') != '')?$request->input('search'):'';
        $countries = Country::where(function($query) use ($search){
            if($search) {
                $searchColumn = ['name', 'sort_name', 'phone_code'];
                foreach ($searchColumn as $singleSearchColumn) {
                    $query->orWhere($singleSearchColumn, "LIKE", '%' . $search . '%');
                }
            }
        });
        $countries = $countries->orderBy($orderBy, $orderOrder)->paginate($limit);
        return view('pages.country.index', compact('countries'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('pages.country.add');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        
        $country = new Country();
        $data  = $request->all();

        $validator = $country->validator($data);
        if ($validator->fails()) {
            return redirect()->back()
            ->withErrors($validator)
            ->withInput();
        }
        
        $fillableFields = $country->getFillable();

        foreach ($data as $field => $value) {
            if (in_array($field, $fillableFields)) {
                if($field == 'sort_name') {
                    $country->sort_name = $value;
                } else {
                    $country->{$field} = $value;
                }
            }
        }

       
        $country->save();
        $request->session()->flash('alert-success', 'Country successfully created');
        return redirect()->route('country.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $country = Country::find(decrypt($id));
        return view('pages.country.show', compact('country'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $country = Country::find(decrypt($id));
        return view('pages.country.edit', compact('country'));
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
        $country = Country::find(decrypt($id));
        $data  = $request->all();

        $validator = $country->validator($data);
        if ($validator->fails()) {
            return redirect()->back()
            ->withErrors($validator)
            ->withInput();
        }
        
        $fillableFields = $country->getFillable();

        foreach ($data as $field => $value) {
            if (in_array($field, $fillableFields)) {
                if($field == 'sort_name') {
                    $country->sort_name = $value;
                } else {
                    $country->{$field} = $value;
                }
            }
        }
        $country->save();
        $request->session()->flash('alert-success', 'Country successfully updated');
        return redirect()->route('country.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        Country::where('id', decrypt($id))->delete();
		\Session::flash('success','Country deleted successfully');
		return redirect(url()->previous());
    }
}
