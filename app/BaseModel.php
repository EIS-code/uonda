<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\DeletedRecord;

class BaseModel extends Model
{
    protected $hidden = ['is_removed', 'created_at', 'updated_at'];

    public static function getTableName()
    {
        return with(new static)->getTable();
    }
}
