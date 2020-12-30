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
        'mime_type', 'attachment', 'url', 'name', 'contacts', 'chat_id'
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
}
