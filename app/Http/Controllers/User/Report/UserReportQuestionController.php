<?php

namespace App\Http\Controllers\User\Report;

use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use App\UserReportQuestion;

class UserReportQuestionController extends BaseController
{
    public function getQuestions(Request $request)
    {
        $model  = new UserReportQuestion();
        $data   = $request->all();
        $userId = $request->get('user_id', false);
        $method = $request->method();

        switch ($method) {
            case 'GET':
                $questions = $model::all();
                break;
            case 'POST':
            case 'PUT':
                $questions = $model::all();
                if (!empty($questions) && !$questions->isEmpty()) {
                    $questions->map(function($question) use($userId) {
                        $question->answer = NULL;

                        $report = $question->report($userId)->first();

                        if (!empty($report)) {
                            $question->answer = $report->answer;
                        }
                    });
                }
                break;
            default:
                $questions = [];
        }

        if (!empty($questions) && !$questions->isEmpty()) {
            return $this->returnSuccess(__('User report questions get successfully!'), $questions);
        }

        return $this->returnNull();
    }
}
