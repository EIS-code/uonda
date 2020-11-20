<?php

namespace App;

use Illuminate\Support\Facades\Validator;

class UserReportQuestion extends BaseModel
{
    protected $fillable = [
        'question',
        'options',
        'question_type'
    ];

    const TYPE_BOOLEAN      = '0';
    const TYPE_RADIO        = '1';
    const TYPE_CHECKBOX     = '2';
    const TYPE_TEXTBOX      = '3';
    const TYPE_TEXTAREA     = '4';
    const TYPE_MULTISELECT  = '5';

    public $questionTypes = [
        self::TYPE_BOOLEAN      => 'Boolean',
        self::TYPE_RADIO        => 'Radio',
        self::TYPE_CHECKBOX     => 'Checkbox',
        self::TYPE_TEXTBOX      => 'Textbox',
        self::TYPE_TEXTAREA     => 'Textarea',
        self::TYPE_MULTISELECT  => 'Multiselect'
    ];

    public function validator(array $data, $id = false, $isUpdate = false)
    {
        return Validator::make($data, [
            'question'      => ['required', 'string'],
            'options'       => ['nullable', 'string'],
            'question_type' => ['nullable', 'in:' . implode(",", array_keys($this->questionTypes))]
        ]);
    }

    public function reports()
    {
        return $this->hasMany('App\UserReport', 'user_report_question_id', 'id');
    }

    public function report(int $userId)
    {
        return $this->hasOne('App\UserReport', 'user_report_question_id', 'id')->where('user_id', $userId);
    }
}
