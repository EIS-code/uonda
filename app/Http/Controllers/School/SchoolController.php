<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use App\School;

class SchoolController extends BaseController
{
    public function getSchool(Request $request)
    {
        $data  = $request->all();
        $model = new School();

        $schools = $model::orderBy('name', 'ASC')->get();

        if (!empty($schools) && !$schools->isEmpty()) {
            return $this->returnSuccess(__('Schools found successfully!'), $schools);
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

        if ($create) {
            return $this->returnSuccess(__('School saved successfully!'), $create);
        }

        return $this->returnError(__('Something went wrong!'));
    }

    public function updateSchool(Request $request)
    {
        $data     = $request->all();
        $model    = new School();
        $schoolId = !empty($data['school_id']) ? (int)$data['school_id'] : false;

        if (empty($schoolId)) {
            return $this->returnError(__('School id not available!'));
        }

        $record = $model::find($schoolId);

        if (empty($record)) {
            return $this->returnError(__('School not found!'));
        }

        $validator = $model->validator($data);

        if ($validator->fails()) {
            return $this->returnError($validator->errors()->first());
        }

        $record->name       = (string)$data['name'];
        $record->city_id    = (int)$data['city_id'];
        $record->country_id = (int)$data['country_id'];

        $update = $record->save();

        if ($update) {
            $record->refresh();

            return $this->returnSuccess(__('School updated successfully!'), $record);
        }

        return $this->returnError(__('Something went wrong!'));
    }
}
