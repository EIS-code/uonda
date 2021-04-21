<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use App\UserBlockProfile;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class UserBlockProfileController extends BaseController
{
    public function block(Request $request)
    {
        $model = new UserBlockProfile();
        $data  = $request->all();
        $now   = Carbon::now();

        $data['is_block']   = $model::IS_BLOCK;
        $data['blocked_by'] = (!empty($data['user_id'])) ? $data['user_id'] : NULL;
        $data['user_ids']   = (!empty($data['user_ids']) && is_array($data['user_ids'])) ? $data['user_ids'] : NULL;

        $validator = $model->validator($data);

        if ($validator->fails()) {
            return $this->returnError($validator->errors()->first());
        }

        $userIds = (array)$data['user_ids'];

        foreach ($userIds as $index => $userId) {
            if ($data['blocked_by'] == $userId) {
                continue;
            }

            $create[$index] = [
                'is_block'   => $model::IS_BLOCK,
                'blocked_by' => $data['blocked_by'],
                'user_id'    => $userId,
                'created_at' => $now,
                'updated_at' => $now
            ];

            $model->updateOrCreate([
                'blocked_by' => $data['blocked_by'],
                'user_id'    => $userId
            ], $create[$index]);
        }

        return $this->returnSuccess(__('Users profile blocked successfully!'));
    }

    public function unBlock(Request $request)
    {
        $model = new UserBlockProfile();
        $data  = $request->all();
        $now   = Carbon::now();

        $data['is_block']   = $model::IS_NOT_BLOCK;
        $data['blocked_by'] = (!empty($data['user_id'])) ? $data['user_id'] : NULL;
        $data['user_ids']   = (!empty($data['user_ids']) && is_array($data['user_ids'])) ? $data['user_ids'] : NULL;

        $validator = $model->validator($data);

        if ($validator->fails()) {
            return $this->returnError($validator->errors()->first());
        }

        $userIds = (array)$data['user_ids'];

        foreach ($userIds as $index => $userId) {
            if ($data['blocked_by'] == $userId) {
                continue;
            }

            $update[$index] = [
                'is_block'   => $model::IS_NOT_BLOCK,
                'updated_at' => $now
            ];

            $model::where('blocked_by', $data['blocked_by'])->where('user_id', $userId)->update($update[$index]);
        }

        return $this->returnSuccess(__('Users profile unblocked successfully!'));
    }

    public function getBlock(Request $request)
    {
        $model     = new UserBlockProfile();
        $userModel = new User();
        $data      = $request->all();

        $userId = (!empty($data['user_id'])) ? $data['user_id'] : NULL;

        if (empty($userId)) {
            return $this->returnError(__('User id is required!'));
        }

        $records = $model::select($model::getTableName() . '.id', $userModel->getTableName() . '.name', $userModel->getTableName() . '.user_name', $model::getTableName() . '.user_id', $userModel->getTableName() . '.profile')
                         ->where('blocked_by', $userId)->where('is_block', $model::IS_BLOCK)
                         ->join($userModel->getTableName(), $model::getTableName() . '.user_id', '=', $userModel->getTableName() . '.id')
                         ->get();

        if (!empty($records) && !$records->isEmpty()) {
            $records->each(function($data) use($userModel) {
                if (!empty($data->profile)) {
                    $storageFolderName = (str_ireplace("\\", "/", $userModel->profile));
                    $data->profile = Storage::disk($userModel->fileSystem)->url($storageFolderName . '/' . $data->profile);
                }
            });

            return $this->returnSuccess(__('Blocked users get successfully!'), $records);
        }

        return $this->returnNull();
    }
}
