<?php

use Illuminate\Database\Seeder;
use App\Country;
use App\State;
use App\City;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $confirmed = $this->command->confirm(__('Are you sure ? Because script will remove all the old Countries, States & Cities data and then add new.'));

        if ($confirmed) {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            Country::truncate();
            State::truncate();
            City::truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            // Countries
            $countriesJson = Storage::disk('public')->get('countries.json');
            $countriesArray = !empty($countriesJson) ? json_decode($countriesJson, true) : [];
            if (json_last_error() === JSON_ERROR_NONE && !empty($countriesArray)) {
                foreach ((array)$countriesArray as $countries) {
                    Country::insert($countries);
                }
            }

            // States
            $statesJson = Storage::disk('public')->get('states.json');
            $statesArray = !empty($statesJson) ? json_decode($statesJson, true) : [];
            if (json_last_error() === JSON_ERROR_NONE && !empty($statesArray)) {
                foreach ((array)$statesArray as $states) {
                    State::insert($states);
                }
            }

            // Cities
            $citiesJson = Storage::disk('public')->get('cities.json');
            $citiesArray = !empty($citiesJson) ? json_decode($citiesJson, true) : [];
            if (json_last_error() === JSON_ERROR_NONE && !empty($citiesArray)) {
                $cityModel = new City();

                foreach ((array)$citiesArray as $cities) {
                    $create = [];

                    foreach ($cities as $city) {
                        if ($cityModel->validator($city, true)) {
                            $create[] = $city;
                        }
                    }

                    foreach (array_chunk($create, 200) as $city) {
                        $cityModel::insert($city);
                    }
                }
            }
        }
    }
}
