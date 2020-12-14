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
        'title', 'sub_title', 'attachment', 'description', 'type'
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

    /**
     * The attributes that should be appends to model object.
     *
     * @var array
     */
    protected $appends = ['encrypted_feed_id'];

    public $fileSystem        = 'public';
    public $storageFolderName = 'feed';

    const TYPE_NULL  = '0';
    const TYPE_IMAGE = '1';
    const TYPE_URL   = '2';
    const TYPE_VIDEO = '3';
    const TYPE_GIF   = '4';
    public $feedTypes = [
        self::TYPE_NULL  => '',
        self::TYPE_IMAGE => 'image',
        self::TYPE_URL   => 'url',
        self::TYPE_VIDEO => 'video',
        self::TYPE_GIF   => 'gif'
    ];

    public function __construct(array $attributes = array())
    {
        parent::__construct($attributes);

        $this->makeVisible('created_at');
    }

    public function validator(array $data, $returnBoolsOnly = false)
    {
        $validator = Validator::make($data, [
            'title'       => ['required', 'string', 'max:255'],
            'sub_title'   => ['nullable', 'string', 'max:255'],
            'attachment'  => ['nullable', 'mimes:' . implode(",", $this->allowedExtensions), 'max:255'],
            'description' => ['required', 'string'],
            'type'        => ['in:' . implode(",", array_keys($this->feedTypes))]
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
        // return Storage::disk($this->fileSystem)->url($storageFolderName . '/' . $this->id . '/' . $value);
        return Storage::disk($this->fileSystem)->url($storageFolderName . '/' . $value);
    }

    public function getCreatedAtAttribute($value)
    {
        if (strtotime($value) <= 0) {
            return $value;
        }

        return strtotime($value) * 1000;
    }

    //get encrypted feed id
    public function getEncryptedFeedIdAttribute()
    {
        return encrypt($this->id);
    }

    public function getTypeAttribute($value)
    {
        if (!isset($value) || !array_key_exists($value, $this->feedTypes)) {
            return $value;
        }

        return $this->feedTypes[$value];
    }
}
