<?php

namespace App;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContactUs extends BaseModel
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'text', 'attachment', 'model_name', 'model_id'
    ];

    /**
     * The attributes that should be appends to model object.
     *
     * @var array
     */
    protected $appends = ['encrypted_contactus_id'];

    public $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];

    public $fileSystem = 'public';
    public $attachmentPath = 'contact_us';

    public function validator(array $data, $returnBoolsOnly = false)
    {
        $validator = Validator::make($data, [
            'text'       => ['required', 'string'],
            'attachment' => ['nullable', 'mimes:' . implode(",", $this->allowedExtensions)],
            'attachment' => ['nullable', 'max:10240'],
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

        $storageFolderName = (str_ireplace("\\", "/", $this->attachmentPath));
        $fileUrl = $storageFolderName . '/' . $this->id . '/' . $value;

        if (File::exists("storage/" . $fileUrl)) {
            return Storage::disk($this->fileSystem)->url($fileUrl);
        }

        return false;
    }

    public function getCreatedAtAttribute($value)
    {
        if (strtotime($value) <= 0) {
            return $value;
        }

        return strtotime($value) * 1000;
    }

    // Get encrypted id.
    public function getEncryptedContactUsIdAttribute()
    {
        return encrypt($this->id);
    }
}
