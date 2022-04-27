<?php

namespace App\Http\Controllers\Location;

use App\Http\Controllers\BaseController;
use App\Country;
use App\City;
use App\State;
use App\User;
use App\UserBlockProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Auth;
use Illuminate\Pagination\LengthAwarePaginator;
use DB;
use Carbon\Carbon;

class LocationController extends BaseController
{
    public function getCountry()
    {
        $countries = Country::all();
        
        if (!empty($countries) && !$countries->isEmpty()) {
            return $this->returnSuccess(__(COUNTRY_FETCHED), $countries);
        }

        return $this->returnNull();
    }

    /*public function getCity(Request $request)
    {
        $countryId = $request->get('country_id', false);
        $stateId = $request->get('state_id', false);
        $per_page = $request->has('per_page') ? $request->per_page : 10;
        $offset = $request->has('offset') ? (int)$request->offset : 0;
        $search = $request->has('search') ? $request->search : '';

        $next_offset = $offset + $per_page;
        $cities_count =  City::count();
        $cities = new City();
        if(!empty($stateId)) {
            $cities = $cities::with('state')->where('state_id', (int)$stateId);
        } else if(!empty($countryId)) {
            $cities = $cities::with('state')->whereHas('state', function($q) use ($countryId) {
                $q->where('country_id', $countryId);
            });
        } 

        if(!empty($search)) {
            $cities = $cities->where('name', 'like', $search.'%');
        }

        $cities = $cities->skip($offset)->take($per_page)->get();
        if($next_offset >= $cities_count) {
            $next_offset = $offset;
        }


        // if(!empty($stateId)) {
        //     $cities = City::with('state')->where('state_id', (int)$stateId)->skip($offset)->take($per_page)->get();
        // } else if (!empty($countryId)) {
        //     $cities = City::with('state')->whereHas('state', function($q) use ($countryId) {
        //         $q->where('country_id', $countryId);
        //     })->skip($offset)->take($per_page)->get();
        // } else {
        //     $cities = City::skip($offset)->take($per_page)->get();
        // }

        if (!empty($cities) && !$cities->isEmpty()) {
            return response()->json([
                'code' => 200,
                'msg'  => __('Cities fetched successfully!'),
                'current_offset' => $offset,
                'next_offset' => $next_offset,
                'per_page' => $per_page,
                'total_cities' => $cities_count,
                'data' => $cities
            ], 200);
        }

        return $this->returnNull();
    }*/

    public function getCity(Request $request, $perPage = 10)
    {
        $countryId  = $request->get('country_id', false);
        $stateId    = $request->get('state_id', false);
        $search     = $request->has('search') ? $request->search : '';
        $pageNumber = $request->has('page_number') ? $request->page_number : 1;

        $cities = new City();

        if(!empty($stateId)) {
            $cities = $cities::with('state')->where('state_id', (int)$stateId);
        } else if(!empty($countryId)) {
            $cities = $cities::with('state')->whereHas('state', function($q) use ($countryId) {
                $q->where('country_id', $countryId);
            });
        }

        if (!empty($search)) {
            $cities = $cities->where('name', 'LIKE', $search . '%');
        }

        $cities = $cities->paginate($perPage, ['*'], 'page', $pageNumber);

        if (!empty($cities) && count($cities) > 0) {
            return $this->returnSuccess(__(CITY_FETCHED), $cities);
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
            return $this->returnSuccess(__(STATE_FETCHED), $states);
        }

        return $this->returnNull();
    }

    //Function to get the cities with user count
    public function getCitiesWithUserCount(Request $request) {
\Log::info("Request : " . json_encode($request->all()));
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
\Log::info("Response : " . json_encode([
            'code' => $status,
            'msg'  => __(CITY_FETCHED),
            'current_offset' => $offset,
            'next_offset' => $next_offset,
            'per_page' => $per_page,
            'total_cities' => $cities_count,
            'data' => $cities
        ]));
        return response()->json([
            'code' => $status,
            'msg'  => __(CITY_FETCHED),
            'current_offset' => $offset,
            'next_offset' => $next_offset,
            'per_page' => $per_page,
            'total_cities' => $cities_count,
            'data' => $cities
        ], 200);
    }

    // Function to get the users listing based on city
    public function getUsersBasedOnCity(Request $request) {
        $modal = new User();

        $rules = [
            'city_id' => 'required'
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return $this->returnError($validator->errors()->first());
        }

        /* $blockedUser = UserBlockProfile::where('is_block' , '1')->pluck('user_id')->toArray();

        if(!empty($blockedUser)) {
            $users = User::where('city_id', $request->city_id)->whereNotIn('id', $blockedUser)->get();
        } else {
            $users = User::where('city_id', $request->city_id)->get();
        } */

        $users = $modal->where('city_id', $request->city_id)->get();

        $users->each(function($userRow) {
            $userRow->setHidden(['encrypted_user_id', 'permissions', 'total_notifications', 'total_read_notifications', 'total_unread_notifications', 'password', 'created_at', 'personal_flag', 'other_flag', 'school_flag', 'remember_token', 'updated_at', 'oauth_uid', 'oauth_provider']);
        });

        $users = $modal->removeBlockedUsers($users);

        return $this->returnSuccess(__(USERS_FETCHED), $users);
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
            'msg'  => __(CITY_FETCHED),
            'current_offset' => $offset,
            'next_offset' => $next_offset,
            'per_page' => $per_page,
            'total_users' => $cities_count,
            'search_cities_count' => $search_cities_count,
            'data' => $cities
        ], 200);
    }

    public function getCitiesBasedOnUsers(Request $request, $perPage = 10)
    {
        $modal      = new City();
        $search     = $request->get('search', NULL);
        $userId     = $request->get('user_id', NULL);
        $isPaginate = $request->get('is_paginate', FALSE);
        $pageNumber = $request->get('page_number', 1);

        $cities = $modal->query();

        if (!empty($search)) {
            $cities->where('name', 'like', $search . '%');
        }

        $cities->select('id', 'state_id', 'name')->whereHas('user', function($whereHas) use($userId) {
            $whereHas->where('id', '!=', $userId);
        });

        if ($isPaginate) {
            $cities = $cities->paginate($perPage, ['*'], 'page', $pageNumber);
        } else {
            $cities = $cities->get();
        }

        if (!empty($cities) && !$cities->isEmpty()) {
            $cities->each(function($cityRow) {
                $cityRow->setHidden(['encrypted_city_id']);
            });

            return $this->returnSuccess(__(CITY_FETCHED), $cities);
        }

        return $this->returnNull();
    }

    /* public function importCountries()
    {
        $results = DB::select("SELECT * FROM `temp_countries`");

        $rows = [];

        $now = Carbon::now();

        foreach ($results as $result) {
            $rows[] = [
                'name'       => $result->countryName,
                'sort_name'  => $result->webCode,
                'latitude'   => $result->latitude,
                'longitude'  => $result->longitude,
                'created_at' => $now
            ];
        }

        $chunked = array_chunk($rows, 500);

        foreach ($chunked as $items) {
            Country::insert($items);
        }
    }

    public function importStates()
    {
        $results = DB::select("SELECT * FROM `temp_states`");

        $rows = $issuedState = [];

        $now = Carbon::now();

        foreach ($results as $result) {
            $country = Country::where('sort_name', $result->countryID)->first();

            if (!empty($country)) {
                $rows[] = [
                    'id'         => $result->stateID,
                    'name'       => $result->stateName,
                    'country_id' => $country->id,
                    'latitude'   => $result->latitude,
                    'longitude'  => $result->longitude,
                    'created_at' => $now
                ];
            } else {
                $issuedState[] = $result;
            }
        }

        if (!empty($issuedState)) {
            echo "Unfounded country list here for state", PHP_EOL;

            dd($issuedState);
        }

        $chunked = array_chunk($rows, 500);

        foreach ($chunked as $items) {
            State::insert($items);
        }
    }

    public function importCities()
    {
        ini_set('memory_limit', '2048M');
        ini_set('max_execution_time', '120');

        $results = DB::select("SELECT * FROM `temp_citiess`");

        $rows = $issuedCity = [];

        $now = Carbon::now();

        foreach ($results as $result) {
            $state = State::where('id', $result->stateID)->first();

            if (!empty($state)) {
                $rows[] = [
                    'name'       => $result->cityName,
                    'state_id'   => $state->id,
                    'latitude'   => $result->latitude,
                    'longitude'  => $result->longitude,
                    'created_at' => $now
                ];
            } else {
                $issuedCity[] = $result;
            }
        }

        dd($issuedCity);

        $chunked = array_chunk($rows, 500);

        foreach ($chunked as $items) {
            City::insert($items);
        }
    } */
}
