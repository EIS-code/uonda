<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use App\UserSetting;
use App\User;
use Illuminate\Support\Facades\Hash;

class UserSettingController extends BaseController
{
    public function accountPrivacy(Request $request)
    {
        $data  = $request->all();
        $model = new UserSetting();

        if (empty($data['user_id']) || !is_numeric($data['user_id'])) {
            return $this->returnError(__('User id seems incorrect.'));
        }

        $userId = (int)$data['user_id'];

        $userSetting = $model::where('user_id', $userId)->first();

        if (empty($userSetting)) {
            $createData['notification'] = $model::NOTIFICATION_ON;
        } else {
            $createData['notification'] = $userSetting->notification;
        }

        $createData['user_id'] = $userId;

        if (isset($data['user_name'])) {
            $createData['user_name'] = (string)$data['user_name'];
        } else {
            $createData['user_name'] = $model::CONSTS_PRIVATE;
        }

        if (isset($data['email'])) {
            $createData['email'] = (string)$data['email'];
        } else {
            $createData['email'] = $model::CONSTS_PRIVATE;
        }

        if (isset($data['screenshot'])) {
            $createData['screenshot'] = (string)$data['screenshot'];
        } else {
            $createData['screenshot'] = $model::SCREENSHOT_OFF;
        }

        $validator = $model->validator($createData);

        if ($validator->fails()) {
            return $this->returnError($validator->errors()->first());
        }

        $create = $model->updateOrCreate(['user_id' => $userId], $createData);

        if ($create) {
            return $this->returnSuccess(__('User account privacy saved successfully!'), $create);
        }

        return $this->returnError(__('Something went wrong!'));
    }

    public function changePassword(Request $request)
    {
        $data  = $request->all();
        $model = new User();

        if (empty($data['user_id']) || !is_numeric($data['user_id'])) {
            return $this->returnError(__('User id seems incorrect.'));
        }

        if (empty($data['old_password'])) {
            return $this->returnError(__('Old password seems incorrect.'));
        }

        if (empty($data['new_password'])) {
            return $this->returnError(__('New password seems incorrect.'));
        }

        if ($data['old_password'] == $data['new_password']) {
            return $this->returnError(__('Old & New password both are same! Please use different.'));
        }

        $userId      = (int)$data['user_id'];
        $oldPassword = $data['old_password'];
        $newPassword = $data['new_password'];

        $requiredFileds = [
            'name'      => ['nullable'],
            'password'  => ['required'],
            'email'     => ['nullable']
        ];

        $validator = $model->validator(['password' => $newPassword], $requiredFileds);

        if ($validator->fails()) {
            return $this->returnError($validator->errors()->first());
        }

        $user = $model::find($userId);

        if (!empty($user)) {
            // Check old entered password.
            if (!Hash::check($oldPassword, $user->password)) {
                return $this->returnError(__('Old password seems incorrect.'));
            }

            $user->password = Hash::make($newPassword);

            if ($user->save()) {
                $user->refresh();

                return $this->returnSuccess(__('User password updated successfully!'), $user);
            }
        }

        return $this->returnError(__('Something went wrong!'));
    }

    public function userNotification(Request $request)
    {
        $data  = $request->all();
        $model = new UserSetting();

        if (empty($data['user_id']) || !is_numeric($data['user_id'])) {
            return $this->returnError(__('User id seems incorrect.'));
        }

        $userId = (int)$data['user_id'];

        $userSetting = $model::where('user_id', $userId)->first();

        if (empty($userSetting)) {
            $createData['user_name']  = $model::CONSTS_PRIVATE;
            $createData['email']      = $model::CONSTS_PRIVATE;
            $createData['screenshot'] = $model::SCREENSHOT_OFF;
        } else {
            $createData['user_name']  = $userSetting->user_name;
            $createData['email']      = $userSetting->email;
            $createData['screenshot'] = $userSetting->screenshot;
        }

        $createData['user_id'] = $userId;

        if (isset($data['notification'])) {
            $createData['notification'] = (string)$data['notification'];
        } else {
            $createData['notification'] = $model::NOTIFICATION_ON;
        }

        $validator = $model->validator($createData);

        if ($validator->fails()) {
            return $this->returnError($validator->errors()->first());
        }

        $create = $model->updateOrCreate(['user_id' => $userId], $createData);

        if ($create) {
            return $this->returnSuccess(__('User notification turn ' . $model->notifications[$create->notification] . ' successfully!'), $create);
        }

        return $this->returnError(__('Something went wrong!'));
    }

    public function userScreenshot(Request $request)
    {
        $data  = $request->all();
        $model = new UserSetting();

        if (empty($data['user_id']) || !is_numeric($data['user_id'])) {
            return $this->returnError(__('User id seems incorrect.'));
        }

        $userId = (int)$data['user_id'];

        $userSetting = $model::where('user_id', $userId)->first();

        if (empty($userSetting)) {
            $createData['user_name']    = $model::CONSTS_PRIVATE;
            $createData['email']        = $model::CONSTS_PRIVATE;
            $createData['notification'] = $model::NOTIFICATION_ON;
        } else {
            $createData['user_name']    = $userSetting->user_name;
            $createData['email']        = $userSetting->email;
            $createData['notification'] = $userSetting->notification;
        }

        $createData['user_id'] = $userId;

        if (isset($data['screenshot'])) {
            $createData['screenshot'] = (string)$data['screenshot'];
        } else {
            $createData['screenshot'] = $model::SCREENSHOT_OFF;
        }

        $validator = $model->validator($createData);

        if ($validator->fails()) {
            return $this->returnError($validator->errors()->first());
        }

        $create = $model->updateOrCreate(['user_id' => $userId], $createData);

        if ($create) {
            return $this->returnSuccess(__('User screenshot turn ' . $model->screenshots[$create->screenshot] . ' successfully!'), $create);
        }

        return $this->returnError(__('Something went wrong!'));
    }

    public function getPrivacy(Request $request)
    {
        $data  = $request->all();
        $model = new UserSetting();

        if (empty($data['user_id']) || !is_numeric($data['user_id'])) {
            return $this->returnError(__('User id seems incorrect.'));
        }

        $userId = (int)$data['user_id'];

        $userSetting = $model::select('user_name', 'email', 'notification', 'screenshot', 'user_id')->where('user_id', $userId)->first();

        if ($userSetting) {
            return $this->returnSuccess(__('User privacy get successfully!'), $userSetting);
        }

        return $this->returnNull(__('User not found!'));
    }
}
