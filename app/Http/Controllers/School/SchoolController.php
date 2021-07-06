<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use App\School;

class SchoolController extends BaseController
{
    public function getSchool(Request $request)
    {
        $data   = $request->all();
        $model  = new School();
        $method = $request->method();
        $country_id = $request->has('country_id') ? $request->country_id : '';

        switch ($method) {
            case 'GET':
                $schools = $model::with('country', 'city', 'state')->orderBy($model::getTableName() . '.name', 'ASC')->get();
                break;
            case 'POST':
                $schools_data = $model::with('country', 'city', 'state');
                if(!empty($country_id)) {
                    $schools_data->whereHas('country', function($q) use ($country_id) {
                        $q->where('country_id', $country_id);
                    });
                }
                $schools = $schools_data->orderBy($model::getTableName() . '.name', 'ASC')->get();
                break;
            case 'PUT':
                $schoolId = $request->get('school_id', false);
                $schools  = $model::with('country', 'city', 'state')->where($model::getTableName() . '.id', (int)$schoolId)->orderBy($model::getTableName() . '.name', 'ASC')->get();
                break;
            default:
                $schools = [];
        }

        if (!empty($schools) && !$schools->isEmpty()) {
            $schools->map(function($data) {
                if (!empty($data->country)) {
                    $data->country_name = $data->country->name;
                }

                unset($data->country);

                if (!empty($data->state)) {
                    $data->state_name = $data->state->name;
                }

                unset($data->state);

                if (!empty($data->city)) {
                    $data->city_name = $data->city->name;
                }

                unset($data->city);
            });

            return $this->returnSuccess(__(SCHOOLS_FOUND), $schools);
        }

        return $this->returnNull();
    }

    public function saveSchool(Request $request)
    {
        $data  = $request->all();
        $model = new School();

        $validator = $model->validator($data);

        if ($validator->fails()) {
            return $this->returnError($validator->errors()->first());
        }

        $create = $model->create($data);
        $school_data = $model->with('country', 'city', 'state')->find($create->id);
        if (!empty($school_data)) {
            $school_data->country_name = $school_data->country->name;
            $school_data->state_name = $school_data->state->name;
            $school_data->city_name = $school_data->city->name;
            unset($school_data->country);
            unset($school_data->state);
            unset($school_data->city);
        }

        if ($create) {
            return $this->returnSuccess(__(SCHOOL_SAVED), $school_data);
        }

        return $this->returnError(__(SOMETHING_WENT_WRONG));
    }

    public function updateSchool(Request $request)
    {
        $data     = $request->all();
        $model    = new School();
        $schoolId = !empty($data['school_id']) ? (int)$data['school_id'] : false;

        if (empty($schoolId)) {
            return $this->returnError(__(SCHOOL_NOT_AVAILABLE));
        }

        $record = $model::find($schoolId);

        if (empty($record)) {
            return $this->returnError(__(SCHOOL_NOT_FOUND));
        }

        $validator = $model->validator($data, $record->id);

        if ($validator->fails()) {
            return $this->returnError($validator->errors()->first());
        }

        $record->name       = (string)$data['name'];
        $record->city_id    = (int)$data['city_id'];
        $record->state_id   = (int)$data['state_id'];
        $record->country_id = (int)$data['country_id'];

        $update = $record->save();

        if ($update) {
            $record->refresh();

            return $this->returnSuccess(__(SCHOOL_UPDATED), $record);
        }

        return $this->returnError(__(SOMETHING_WENT_WRONG));
    }
}
