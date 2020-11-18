<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use App\User;
use App\UserDocument;
use App\UserSetting;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\UploadedFile;
use Log;

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

        $data['personal_flag'] = $model::PERSONAL_FLAG_DONE;

        $validator = $model->validator($data);

        if ($validator->fails()) {
            return $this->returnError($validator->errors()->first());
        }

        $data['password'] = !empty($data['password']) ? Hash::make($data['password']) : NULL;

        $create = $model->create($data);

        if ($create) {
            $userId = $create->id;

            // Privacy
            UserSetting::create(['user_id' => $userId, 'user_name' => UserSetting::CONSTS_PRIVATE, 'email' => UserSetting::CONSTS_PRIVATE, 'notification' => UserSetting::NOTIFICATION_ON]);

            $user = $model::find($userId);

            return $this->returnSuccess(__('User personal details saved successfully!'), $user);
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

        $user->school_id   = (int)$data['school_id'];
        $user->school_flag = $model::SCHOOL_FLAG_DONE;

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
            'birthday'         => ['nullable'],
            'country_id'       => ['nullable'],
            'city_id'          => ['nullable']
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
        $user->other_flag       = $model::OTHER_FLAG_DONE;
        $user->country_id       = !empty($data['country_id']) ? $data['country_id'] : NULL;
        $user->city_id          = !empty($data['city_id']) ? $data['city_id'] : NULL;

        if ($user->save()) {
            $user->refresh();

            return $this->returnSuccess(__('User other details saved successfully!'), $user);
        }

        return $this->returnError(__('Something went wrong!'));
    }

    public function registrationDocument(Request $request)
    {
        $data  = $request->all();
        Log::info($data);
        $model = new UserDocument();

        $validator = $model->validator($data);

        if ($validator->fails()) {
            return $this->returnError($validator->errors()->first());
        }

        $userId = (int)$data['user_id'];

        $document = $data['document'];

        Log::info($document);

        if ($document instanceof UploadedFile) {
            $pathInfos = pathinfo($document->getClientOriginalName());
            Log::info($pathInfos);

            if (!empty($pathInfos['extension'])) {
                $folder = false;

                if ($data['document_type'] == $model::GRADUATION_CERTIFICATE) {
                    $folder = $model->graduation;
                } elseif ($data['document_type'] == $model::STUDENT_ID_CARD) {
                    $folder = $model->studentIdCard;
                } elseif ($data['document_type'] == $model::PHOTO_IN_UNIFORM) {
                    $folder = $model->photoInUniform;
                } elseif ($data['document_type'] == $model::CLASS_PHOTO) {
                    $folder = $model->classPhoto;
                }

                Log::info($folder);

                if (!empty($folder)) {
                    $fileName  = (empty($pathInfos['filename']) ? time() : $pathInfos['filename']) . '_' . time() . '.' . $pathInfos['extension'];
                    $storeFile = $document->storeAs($folder, $fileName, $model->fileSystem);

                    if ($storeFile) {
                        $save = $model->updateOrCreate(['document_type' => $data['document_type'], 'user_id' => $userId], ['document_type' => $data['document_type'], 'document' => $fileName, 'user_id' => $userId]);

                        if ($save) {
                            $user = User::find($userId);

                            return $this->returnSuccess(__('User document saved successfully!'), $user);
                        }
                    }
                }
            }
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

    public function getDetails(Request $request)
    {
        $model = new User();
        $data  = $request->all();

        if (empty($data['user_id']) || !is_numeric($data['user_id'])) {
            return $this->returnError(__('User id seems incorrect.'));
        }

        $userId = (int)$data['user_id'];

        $user   = $model::find($userId);

        if (!empty($user)) {
            return $this->returnSuccess(__('User details get successfully!'), $user);
        }

        return $this->returnNull();
    }

    public function getStatus(Request $request)
    {
        $model = new User();
        $data  = $request->all();

        if (empty($data['user_id']) || !is_numeric($data['user_id'])) {
            return $this->returnError(__('User id seems incorrect.'));
        }

        $userId = (int)$data['user_id'];

        $user = $model::select('personal_flag', 'school_flag', 'other_flag')->find($userId);

        if (!empty($user)) {
            $user->makeVisible(['personal_flag', 'school_flag', 'other_flag']);

            return $this->returnSuccess(__('User details get successfully!'), $user);

            $user->makeHidden(['personal_flag', 'school_flag', 'other_flag']);
        }

        return $this->returnNull();
    }

    public function profileUpdate(Request $request)
    {
        $model = new User();
        $data  = $request->all();

        if (empty($data['user_id']) || !is_numeric($data['user_id'])) {
            return $this->returnError(__('User id seems incorrect.'));
        }

        $userId = (int)$data['user_id'];

        unset($data['user_id']);

        if (isset($data['password'])) {
            $data['password'] = !empty($data['password']) ? Hash::make($data['password']) : NULL;
        }

        $user = $model::find($userId);

        if (!empty($user)) {
            $fillableFields = $model->getFillable();

            $requiredFileds = [
                'name'      => ['nullable'],
                'password'  => ['nullable'],
                'email'     => ['nullable']
            ];

            foreach ($data as $field => $value) {
                if (in_array($field, $fillableFields)) {
                    $requiredFileds[$field] = ['required'];
                }
            }

            $validator = $model->validator($data, $requiredFileds);

            if ($validator->fails()) {
                return $this->returnError($validator->errors()->first());
            }

            foreach ($data as $field => $value) {
                if (in_array($field, $fillableFields)) {
                    $user->{$field} = $value;
                }
            }

            if ($user->save()) {
                $user->refresh();

                return $this->returnSuccess(__('User profile details updated successfully!'), $user);
            }
        }

        return $this->returnNull();
    }
}
