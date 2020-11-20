<?php

namespace App\Http\Controllers\User\Report;

use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use App\UserReportQuestion;

class UserReportQuestionController extends BaseController
{
    public function getQuestions()
    {
        $model = new UserReportQuestion();

        $questions = $model::all();

        if (!empty($questions) && !$questions->isEmpty()) {
            return $this->returnSuccess(__('User report questions get successfully!'), $questions);
        }

        return $this->returnNull();
    }
}
