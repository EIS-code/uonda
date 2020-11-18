<?php

namespace App;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class Feed extends BaseModel
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title', 'sub_title', 'attachment', 'description'
    ];

    public $allowedExtensions = [
        'jpg',
        'jpeg',
        'png',
        'gif',
        'txt',
        'doc',
        'pdf',
        'docx',
        'flv',
        'mp4',
        'm3u8',
        'ts',
        '3gp',
        'mov',
        'avi',
        'wmv'
    ];

    public $fileSystem        = 'public';
    public $storageFolderName = 'feed';

    public function validator(array $data, $returnBoolsOnly = false)
    {
        $validator = Validator::make($data, [
            'title'       => ['required', 'string', 'max:255'],
            'sub_title'   => ['nullable', 'string', 'max:255'],
            'attachment'  => ['nullable', 'mimes:' . implode(",", $this->allowedExtensions), 'max:255'],
            'description' => ['required', 'string']
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

        $storageFolderName = (str_ireplace("\\", "/", $this->storageFolderName));
        return Storage::disk($this->fileSystem)->url($storageFolderName . '/' . $this->id . '/' . $value);
    }
}
