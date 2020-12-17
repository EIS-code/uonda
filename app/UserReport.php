<?php

namespace App;

use App\User;
use App\UserReportQuestion;
use Illuminate\Support\Facades\Validator;

class UserReport extends BaseModel
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'answer', 'user_report_question_id', 'user_id', 'reported_by'
    ];

    public function validator(array $data, $returnBoolsOnly = false)
    {
        // Get question.
        $answer = [];
        if (!empty($data['user_report_question_id'])) {
            $questionId = (int)$data['user_report_question_id'];

            $question = UserReportQuestion::find($questionId);

            if ($question) {
                switch($question->question_type) {
                    case UserReportQuestion::TYPE_BOOLEAN:
                        $answer = ['boolean'];
                        break;
                    case UserReportQuestion::TYPE_RADIO:
                        /* TODO */
                        break;
                    case UserReportQuestion::TYPE_CHECKBOX:
                        /* TODO */
                        break;
                    case UserReportQuestion::TYPE_TEXTBOX:
                        /* TODO */
                        break;
                    case UserReportQuestion::TYPE_TEXTAREA:
                        /* TODO */
                        break;
                    case UserReportQuestion::TYPE_MULTISELECT:
                        /* TODO */
                        break;
                    default:
                        break;
                }
            }
        }

        $validator = Validator::make($data, [
            'answer'                  => array_merge(['required'], $answer),
            'user_report_question_id' => ['required', 'integer', 'exists:' . (new UserReportQuestion())->getTableName() . ',id'],
            'user_id'                 => ['required', 'integer', 'exists:' . (new User())->getTableName() . ',id'],
            'reported_by'             => ['required', 'integer', 'exists:' . (new User())->getTableName() . ',id']
        ]);

        if ($returnBoolsOnly === true) {
            if ($validator->fails()) {
                \Session::flash('error', $validator->errors()->first());
            }

            return !$validator->fails();
        }

        return $validator;
    }
}
