<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\DeletedRecord;
use App\User;

class BaseModel extends Model
{
    protected $hidden = ['is_removed', 'created_at', 'updated_at'];

    public static function getTableName()
    {
        return with(new static)->getTable();
    }

    public function appendNewFields($fields)
    {
        if (is_array($fields)) {
            array_merge($fields, $this->appends);
        } elseif (is_string($fields)) {
            array_push($this->appends, $fields);
        }
    }

    public function newQuery() {
        /*try {
            $tableFillables = $this->fillable;
            $tableColumns   = \Schema::getColumnListing(parent::getTable());
            $where          = [];
            $userModal      = new User();

            $userId        = (int)request()->get('user_id', false);
            $requestUserId = (int)request()->get('request_user_id', false);

            // Check if blocked.
            if ($userModal->isBlocked($userId, $requestUserId)) {
                
            }
        } catch(Exception $exception) {}*/

        return parent::newQuery();
    }
}
