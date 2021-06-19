<?php

namespace App;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class ContactUs extends BaseModel
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'text', 'attachment', 'model_name', 'model_id'
    ];

    public $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];

    public $fileSystem = 'public';
    public $attachment = 'contact_us';

    public function validator(array $data, $returnBoolsOnly = false)
    {
        $validator = Validator::make($data, [
            'text'       => ['required', 'string'],
            'attachment' => ['nullable', 'mimes:' . implode(",", $this->allowedExtensions) .'|max:2048'],
            'model_name' => ['required', 'string', 'max:255'],
            'model_id'   => ['required', 'integer']
        ]);

        if ($returnBoolsOnly === true) {
            if ($validator->fails()) {
                \Session::flash('error', $validator->errors()->first());
            }

            return !$validator->fails();
        }

        return $validator;
    }

    public function getAttachmentAttribute($value)
    {
        if (empty($value)) {
            return $value;
        }

        $storageFolderName = (str_ireplace("\\", "/", $this->attachment));

        return Storage::disk($this->fileSystem)->url($storageFolderName . '/' . $this->id . '/' . $value);
    }
}
