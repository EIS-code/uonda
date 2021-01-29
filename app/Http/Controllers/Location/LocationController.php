<?php

namespace App\Http\Controllers\Location;

use App\Http\Controllers\BaseController;
use App\Country;
use App\City;
use App\State;
use Illuminate\Http\Request;

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

        if (!empty($countryId)) {
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
}
