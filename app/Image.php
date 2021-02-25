<?php

namespace App;

use Illuminate\Support\Facades\Storage;

class Image extends BaseModel
{
    protected $fillable = [
        'path'
    ];

    protected $hidden = ['created_at', 'updated_at'];

    public $fileSystem = 'public';
    public $imagePath  = 'test\\';

    public function getPathAttribute($value)
    {
        if (empty($value)) {
            return $value;
        }

        $imagePath = (str_ireplace("\\", "/", $this->imagePath));
        return Storage::disk($this->fileSystem)->url($imagePath . $value);
    }
}
