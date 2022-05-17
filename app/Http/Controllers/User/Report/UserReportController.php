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

        $data['reported_by'] = $data['user_id'];
        $data['user_id']     = !empty($data['request_user_id']) ? (int)$data['request_user_id'] : NULL;

        $validator = $model->validator($data);

        if ($validator->fails()) {
            return $this->returnError($validator->errors()->first());
        }

        $create = $model->updateOrCreate(['user_report_question_id' => $data['user_report_question_id'], 'user_id' => $data['user_id']], $data);

        if ($create) {
            return $this->returnSuccess(__(USER_REPORT_SAVED), $create);
        }

        return $this->returnError(__(SOMETHING_WENT_WRONG));
    }
}
