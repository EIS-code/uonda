<?php

namespace App;

use App\User;

class UserDocument extends BaseModel
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'document_type', 'document', 'user_id'
    ];

    const GRADUATION_CERTIFICATE = '0';
    const STUDENT_ID_CARD        = '1';
    const PHOTO_IN_UNIFORM       = '2';
    const CLASS_PHOTO            = '3';
    public $documentTypes = [
        self::GRADUATION_CERTIFICATE => 'Graduation Certificate',
        self::STUDENT_ID_CARD        => 'Student Id Card',
        self::PHOTO_IN_UNIFORM       => 'Photo In Uniform',
        self::CLASS_PHOTO            => 'Class Photo'
    ];

    public $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];

    public $fileSystem     = 'public';
    public $graduation     = 'user\\document\\graduation';
    public $studentIdCard  = 'user\\document\\id_card';
    public $photoInUniform = 'user\\document\\photo_in_uniform';
    public $classPhoto     = 'user\\document\\class_photo';

    public function validator(array $data, $returnBoolsOnly = false)
    {
        $validator = Validator::make($data, [
            'document_type' => ['required', 'in:' . implode(",", array_keys($this->documentTypes))],
            'document'      => ['required', 'string', 'mimes:' . implode(",", $this->allowedExtensions), 'max:255'],
            'user_id'       => ['required', 'integer', 'exists:' . User::getTableName() . ',id']
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
