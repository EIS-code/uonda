<?php

namespace App;

use App\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserDocument extends BaseModel
{
    use SoftDeletes;

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

    public $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'txt', 'doc', 'pdf', 'docx'];

    public $fileSystem     = 'public';
    public $graduation     = 'user\\document\\graduation';
    public $studentIdCard  = 'user\\document\\id_card';
    public $photoInUniform = 'user\\document\\photo_in_uniform';
    public $classPhoto     = 'user\\document\\class_photo';

    public function validator(array $data, $returnBoolsOnly = false)
    {
        $validator = Validator::make($data, [
            'document_type' => ['required', 'in:' . implode(",", array_keys($this->documentTypes))],
            'document'      => ['required', 'mimes:' . implode(",", $this->allowedExtensions)],
            'user_id'       => ['required', 'integer', 'exists:' . (new User())->getTableName() . ',id']
        ]);

        if ($returnBoolsOnly === true) {
            if ($validator->fails()) {
                \Session::flash('error', $validator->errors()->first());
            }

            return !$validator->fails();
        }

        return $validator;
    }

    public function getDocumentAttribute($value)
    {
        if (empty($value)) {
            return $value;
        }

        $storageFolderName = false;

        if ($this->document_type == self::GRADUATION_CERTIFICATE) {
            $storageFolderName = (str_ireplace("\\", "/", $this->graduation));
        } elseif ($this->document_type == self::STUDENT_ID_CARD) {
            $storageFolderName = (str_ireplace("\\", "/", $this->studentIdCard));
        } elseif ($this->document_type == self::PHOTO_IN_UNIFORM) {
            $storageFolderName = (str_ireplace("\\", "/", $this->photoInUniform));
        } elseif ($this->document_type == self::CLASS_PHOTO) {
            $storageFolderName = (str_ireplace("\\", "/", $this->classPhoto));
        }

        if (!empty($storageFolderName)) {
            return Storage::disk($this->fileSystem)->url($storageFolderName . '/' . $value);
        }

        return $value;
    }
}
