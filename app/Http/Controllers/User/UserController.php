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

    public function registrationPersonal(Request $request)
    {
        $data  = $request->all();
        $model = new User();

        $data['password'] = !empty($data['password']) ? Hash::make($data['password']) : NULL;

        $validator = $model->validator($data);

        if ($validator->fails()) {
            return $this->returnError($validator->errors()->first());
        }

        $create = $model->create($data);

        if ($create) {
            return $this->returnSuccess(__('User personal details saved successfully!'), $create);
        }

        return $this->returnError(__('Something went wrong!'));
    }

    public function registrationSchool(Request $request)
    {
        $data  = $request->all();
        $model = new User();

        $requiredFileds = [
            'name'      => ['nullable'],
            'password'  => ['nullable'],
            'email'     => ['nullable'],
            'school_id' => ['required']
        ];

        $extraFields = [
            'user_id' => ['required', 'integer', 'exists:' . $model->getTableName() . ',id']
        ];

        $validator = $model->validator($data, $requiredFileds, $extraFields);

        if ($validator->fails()) {
            return $this->returnError($validator->errors()->first());
        }

        $user = $model::find($data['user_id']);

        $user->school_id = (int)$data['school_id'];

        if ($user->save()) {
            $user->refresh();

            return $this->returnSuccess(__('User school details saved successfully!'), $user);
        }

        return $this->returnError(__('Something went wrong!'));
    }

    public function registrationOther(Request $request)
    {
        $data  = $request->all();
        $model = new User();

        $extraRequired = [];
        if (!empty($data['current_status']) && is_integer($data['current_status'])) {
            if ($data['current_status'] == 1) {
                $extraRequired = [
                    'company'      => ['required'],
                    'job_position' => ['required']
                ];
            } elseif ($data['current_status'] == 2) {
                $extraRequired = [
                    'university'     => ['required'],
                    'field_of_study' => ['required']
                ];
            }
        }

        $requiredFileds = array_merge([
            'name'             => ['nullable'],
            'password'         => ['nullable'],
            'email'            => ['nullable'],
            'current_location' => ['required'],
            'nation'           => ['required'],
            'gender'           => ['required'],
            'birthday'         => ['nullable']
        ], $extraRequired);

        $extraFields = [
            'user_id' => ['required', 'integer', 'exists:' . $model->getTableName() . ',id']
        ];

        $validator = $model->validator($data, $requiredFileds, $extraFields);

        if ($validator->fails()) {
            return $this->returnError($validator->errors()->first());
        }

        $user = $model::find($data['user_id']);

        $user->current_location = $data['current_location'];
        $user->nation           = $data['nation'];
        $user->gender           = $data['gender'];
        $user->birthday         = !empty($data['birthday']) ? $data['birthday'] : NULL;
        $user->current_status   = isset($data['current_status']) ? (int)$data['current_status'] : 0;
        $user->company          = !empty($data['company']) ? $data['company'] : NULL;
        $user->job_position     = !empty($data['job_position']) ? $data['job_position'] : NULL;
        $user->university       = !empty($data['university']) ? $data['university'] : NULL;
        $user->field_of_study   = !empty($data['field_of_study']) ? $data['field_of_study'] : NULL;

        if ($user->save()) {
            $user->refresh();

            return $this->returnSuccess(__('User other details saved successfully!'), $user);
        }

        return $this->returnError(__('Something went wrong!'));
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

        $user = $model->where('email', $userName)->first();

        if (!empty($user) && Hash::check($password, $user->password)) {
            return $this->returnSuccess(__('Logged in successfully!'), $user);
        }

        return $this->returnError(__('Username or Password is incorrect.'));
    }
}
