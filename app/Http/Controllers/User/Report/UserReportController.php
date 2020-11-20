<?php

namespace App\Http\Controllers\User\Report;

use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use App\UserReport;

class UserReportController extends BaseController
{
    public function postAnswer(Request $request)
    {
        $model = new UserReport();
        $data  = $request->all();

        $validator = $model->validator($data);

        if ($validator->fails()) {
            return $this->returnError($validator->errors()->first());
        }

        $create = $model->updateOrCreate(['user_report_question_id' => $data['user_report_question_id'], 'user_id' => $data['user_id']], $data);

        if ($create) {
            return $this->returnSuccess(__('User report saved successfully!'), $create);
        }

        return $this->returnError(__('Something went wrong!'));
    }
}
