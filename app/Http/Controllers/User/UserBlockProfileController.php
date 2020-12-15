<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use App\UserBlockProfile;
use Carbon\Carbon;

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

        foreach ($userIds as $userId) {
            if ($data['blocked_by'] == $userId) {
                continue;
            }

            $create[] = [
                'is_block'   => $model::IS_BLOCK,
                'blocked_by' => $data['blocked_by'],
                'user_id'    => $userId,
                'created_at' => $now,
                'updated_at' => $now
            ];

            $model->updateOrCreate([
                'blocked_by' => $data['blocked_by'],
                'user_id'    => $userId
            ], $create);
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
}
