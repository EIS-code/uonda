<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use App\User;
use App\UserDocument;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\UploadedFile;

class UserController extends BaseController
{
    public function registration(Request $request)
    {
        $data          = $request->all();
        $model         = new User();
        $modelDocument = new UserDocument();

        $data['password'] = !empty($data['password']) ? Hash::make($data['password']) : NULL;

        $validator = $model->validator($data);

        if ($validator->fails()) {
            return $this->returnError($validator->errors()->first());
        }

        $create = $model->create($data);

        if ($create) {
            $userId = $create->id;

            if (isset($data['document_type']) && array_key_exists($data['document_type'], $modelDocument->documentTypes)) {
                if (!empty($data['document_graduation'])) {
                    $documentGraduation = $data['document_graduation'];

                    if ($documentGraduation instanceof UploadedFile) {
                        $pathInfos = pathinfo($documentGraduation->getClientOriginalName());

                        if (!empty($pathInfos['extension'])) {
                            $fileName  = (empty($pathInfos['filename']) ? time() : $pathInfos['filename']) . '_' . time() . '.' . $pathInfos['extension'];
                            $storeFile = $documentGraduation->storeAs($modelDocument->graduation, $fileName, $modelDocument->fileSystem);

                            if ($storeFile) {
                                $modelDocument->create(['document_type' => $modelDocument::GRADUATION_CERTIFICATE, 'document' => $fileName, 'user_id' => $userId]);
                            }
                        }
                    }
                }

                if (!empty($data['document_student_id_card'])) {
                    $documentStudentIdCard = $data['document_student_id_card'];

                    if ($documentStudentIdCard instanceof UploadedFile) {
                        $pathInfos = pathinfo($documentStudentIdCard->getClientOriginalName());

                        if (!empty($pathInfos['extension'])) {
                            $fileName  = (empty($pathInfos['filename']) ? time() : $pathInfos['filename']) . '_' . time() . '.' . $pathInfos['extension'];
                            $storeFile = $documentStudentIdCard->storeAs($modelDocument->studentIdCard, $fileName, $modelDocument->fileSystem);

                            if ($storeFile) {
                                $modelDocument->create(['document_type' => $modelDocument::STUDENT_ID_CARD, 'document' => $fileName, 'user_id' => $userId]);
                            }
                        }
                    }
                }

                if (!empty($data['document_photo_in_uniform'])) {
                    $documentPhotoInUniform = $data['document_photo_in_uniform'];

                    if ($documentPhotoInUniform instanceof UploadedFile) {
                        $pathInfos = pathinfo($documentPhotoInUniform->getClientOriginalName());

                        if (!empty($pathInfos['extension'])) {
                            $fileName  = (empty($pathInfos['filename']) ? time() : $pathInfos['filename']) . '_' . time() . '.' . $pathInfos['extension'];
                            $storeFile = $documentPhotoInUniform->storeAs($modelDocument->photoInUniform, $fileName, $modelDocument->fileSystem);

                            if ($storeFile) {
                                $modelDocument->create(['document_type' => $modelDocument::PHOTO_IN_UNIFORM, 'document' => $fileName, 'user_id' => $userId]);
                            }
                        }
                    }
                }

                if (!empty($data['document_class_photo'])) {
                    $documentClassPhoto = $data['document_class_photo'];

                    if ($documentClassPhoto instanceof UploadedFile) {
                        $pathInfos = pathinfo($documentClassPhoto->getClientOriginalName());

                        if (!empty($pathInfos['extension'])) {
                            $fileName  = (empty($pathInfos['filename']) ? time() : $pathInfos['filename']) . '_' . time() . '.' . $pathInfos['extension'];
                            $storeFile = $documentClassPhoto->storeAs($modelDocument->classPhoto, $fileName, $modelDocument->fileSystem);

                            if ($storeFile) {
                                $modelDocument->create(['document_type' => $modelDocument::CLASS_PHOTO, 'document' => $fileName, 'user_id' => $userId]);
                            }
                        }
                    }
                }
            }

            return $this->returnSuccess(__('User registration done successfully!'), $create);
        }

        return $this->returnNull();
    }

    public function doLogin(Request $request)
    {
        $model = new User();
        $data  = $request->all();

        $userName = !empty($data['username']) ? $data['username'] : NULL;
        $password = !empty($data['password']) ? $data['password'] : NULL;

        if (empty($userName) || empty($password)) {
            return $this->returnError(__('Username or Password is incorrect.'));
        }

        $user = $model->where('name', $userName)->first();

        if (!empty($user) && Hash::check($password, $user->password)) {
            return $this->returnSuccess(__('Logged in successfully!'), $user);
        }

        return $this->returnError(__('Username or Password is incorrect.'));
    }
}
