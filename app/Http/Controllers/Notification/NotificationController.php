<?php

namespace App\Http\Controllers\Notification;

use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use App\Notification;
use App\User;
use DB;
use ReflectionClass;

class NotificationController extends BaseController
{
    public function storeScreenshot(Request $request)
    {
        $data  = $request->all();
        $model = new Notification();

        $modelName = new ReflectionClass((new User));
        $modelName = $modelName->getName();

        $data['message']  = __('Screenshot captured.');
        $data['model']    = $modelName;
        $data['model_id'] = $data['user_id'];

        $validator = $model->validator($data);

        if ($validator->fails()) {
            return $this->returnError($validator->errors()->first());
        }

        $create = $model->create($data);

        if ($create) {
            return $this->returnSuccess(__('Notification create successfully!'), $create);
        }

        return $this->returnNull();
    }
}
