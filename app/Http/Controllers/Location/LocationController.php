<?php

namespace App\Http\Controllers\Location;

use App\Http\Controllers\BaseController;
use App\Country;
use App\City;
use App\State;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LocationController extends BaseController
{
    public function getCountry()
    {
        $countries = Country::all();

        if (!empty($countries) && !$countries->isEmpty()) {
            return $this->returnSuccess(__('Country fetched successfully!'), $countries);
        }

        return $this->returnNull();
    }

    public function getCity(Request $request)
    {
        $countryId = $request->get('country_id', false);
        $stateId = $request->get('state_id', false);

        if(!empty($stateId)) {
            $cities = City::with('state')->where('state_id', (int)$stateId)->get();
        } else if (!empty($countryId)) {
            $cities = City::with('state')->whereHas('state', function($q) use ($countryId) {
                $q->where('country_id', $countryId);
            })->get();
        } else {
            $cities = City::all();
        }

        if (!empty($cities) && !$cities->isEmpty()) {
            return $this->returnSuccess(__('City fetched successfully!'), $cities);
        }

        return $this->returnNull();
    }

    public function getState(Request $request)
    {
        $countryId = $request->get('country_id', false);

        if (!empty($countryId)) {
            $states = State::where('country_id', (int)$countryId)->get();
        } else {
            $states = State::all();
        }

        if (!empty($states) && !$states->isEmpty()) {
            return $this->returnSuccess(__('State fetched successfully!'), $states);
        }

        return $this->returnNull();
    }

    //Function to get the cities with user count
    public function getCitiesWithUserCount(Request $request) {
        $per_page = $request->has('per_page') ? $request->per_page : 10;
        $offset = $request->has('offset') ? (int)$request->offset : 0;
        $with_pagination = $request->has('with_pagination') ? (int)$request->with_pagination : 0;
        $status = 200;
        $next_offset = $offset + $per_page;
        $cities_count = City::count();
        if($with_pagination == 0) {
            $cities = City::withCount('Users')->having('users_count', '>', 0)->get();
        } else {
            $cities = City::withCount('Users')->having('users_count', '>', 0)->skip($offset)->take($per_page)->get();
        }
        
        if($next_offset >= $cities_count) {
            $next_offset = $offset;
        }
        $cities->each(function($userRow) {
            $userRow->setHidden(['encrypted_city_id', 'created_at', 'updated_at', 'state_id']);
        });
        return response()->json([
            'code' => $status,
            'msg'  => __('Cities fetched successfully!'),
            'current_offset' => $offset,
            'next_offset' => $next_offset,
            'per_page' => $per_page,
            'total_cities' => $cities_count,
            'data' => $cities
        ], 200);
    }

    //Function to get the users listing based on city
    public function getUsersBasedOnCity(Request $request) {
        $rules = [
            'city_id' => 'required'
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return $this->returnError($validator->errors()->first());
        }
        $users = User::where('city_id', $request->city_id)->get();
        $users->each(function($userRow) {
            $userRow->setHidden(['encrypted_user_id', 'permissions', 'total_notifications', 'total_read_notifications', 'total_unread_notifications', 'password', 'created_at', 'personal_flag', 'other_flag', 'school_flag', 'remember_token', 'updated_at', 'oauth_uid', 'oauth_provider']);
        });
        return $this->returnSuccess(__('Users fetched successfully!'), $users);
    }

    //Function to get all the cities with search and pagination
    public function getAllCities(Request $request) {
        $per_page = $request->has('per_page') ? $request->per_page : 10;
        $offset = $request->has('offset') ? (int)$request->offset : 0;
        $search = $request->has('search') ? $request->search : '';
        $status = 200;
        $next_offset = $offset + $per_page;
        $search_cities_count = 0;

        $cities_count =  City::count();
        $cities = new City();
        if(!empty($search)) {
            $cities = $cities::with(['State.Country'])->where('name', 'like', '%'.$search.'%');
            $search_cities_count = $cities->count();
        }
        $cities = $cities->skip($offset)->take($per_page)->get();
        if(!empty($search) && ($next_offset >= $search_cities_count)) {
            $next_offset = $offset;
        } else {
            if($next_offset >= $cities_count) {
                $next_offset = $offset;
            }
        }
        return response()->json([
            'code' => $status,
            'msg'  => __('Cities fetched successfully!'),
            'current_offset' => $offset,
            'next_offset' => $next_offset,
            'per_page' => $per_page,
            'total_users' => $cities_count,
            'search_cities_count' => $search_cities_count,
            'data' => $cities
        ], 200);
    }
}
