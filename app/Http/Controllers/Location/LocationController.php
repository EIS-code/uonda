<?php

namespace App\Http\Controllers\Location;

use App\Http\Controllers\BaseController;
use App\Country;
use App\City;
use App\State;

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

    public function getCity()
    {
        $cities = City::all();

        if (!empty($cities) && !$cities->isEmpty()) {
            return $this->returnSuccess(__('City fetched successfully!'), $cities);
        }

        return $this->returnNull();
    }

    public function getState()
    {
        $states = State::all();

        if (!empty($states) && !$states->isEmpty()) {
            return $this->returnSuccess(__('State fetched successfully!'), $states);
        }

        return $this->returnNull();
    }
}
