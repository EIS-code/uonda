<?php

namespace App;

use Illuminate\Support\Facades\Validator;
use App\Chat;
use Illuminate\Support\Facades\Storage;

class ChatAttachment extends BaseModel
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'mime_type', 'attachment', 'url', 'address', 'name', 'contacts', 'chat_id'
    ];

    public $fileSystem = 'public';
    public $folder     = 'user\\chat\\attachment';

    public function validators(array $data, $returnBoolsOnly = false)
    {
        $validator = Validator::make($data, [
            'chat_id' => ['required', 'integer', 'exists:' . (new Chat())->getTableName() . ',id']
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

        $storageFolderName = (str_ireplace("\\", "/", $this->folder));
        return Storage::disk($this->fileSystem)->url($storageFolderName . '/' . $this->chat_id . '/' . $value);
    }

    public function getCreatedAtAttribute($value)
    {
        if (empty($value)) {
            return $value;
        }

        return strtotime($value) * 1000;
    }

    public function getUpdatedAtAttribute($value)
    {
        if (empty($value)) {
            return $value;
        }

        return strtotime($value) * 1000;
    }
}
