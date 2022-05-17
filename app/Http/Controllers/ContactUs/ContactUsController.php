<?php

namespace App\Http\Controllers\ContactUs;

use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use App\ContactUs;
use App\User;
use ReflectionClass;
use Illuminate\Http\UploadedFile;

class ContactUsController extends BaseController
{
    public function store(Request $request)
    {
        $model = new ContactUs();
        $data  = $request->all();

        $modelName = new ReflectionClass((new User));
        $modelName = $modelName->getName();

        $data['model_name'] = $modelName;

        if (!empty($data['user_id'])) {
            $data['model_id'] = (int)$data['user_id'];
        }

        $validator = $model->validator($data);

        if ($validator->fails()) {
            return $this->returnError($validator->errors()->first());
        }

        $fileName   = NULL;
        $attachment = (!empty($data['attachment'])) ? $data['attachment'] : NULL;

        if (!empty($attachment) && $attachment instanceof UploadedFile) {
            $pathInfos = pathinfo($attachment->getClientOriginalName());

            if (!empty($pathInfos['extension'])) {
                $fileName  = (empty($pathInfos['filename']) ? time() : $pathInfos['filename']) . '_' . time() . '.' . $pathInfos['extension'];
                $fileName  = removeSpaces($fileName);
                $data['attachment'] = $fileName;
            }
        }

        $create = $model->create($data);

        if ($create) {
            $id = $create->id;

            if (!empty($attachment) && $attachment instanceof UploadedFile) {
                $folder = $model->attachmentPath . '/' . $id;

                $storeFile = $attachment->storeAs($folder, $fileName, $model->fileSystem);

                if (!$storeFile) {
                    $record = $model->find($id);
                    $record->update(['attachment' => NULL]);
                }
            }

            return $this->returnSuccess(__(CONTACT_US_SAVED), $create->refresh());
        }

        return $this->returnNull();
    }
}
