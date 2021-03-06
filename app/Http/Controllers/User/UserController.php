<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use App\User;
use App\UserDocument;
use App\UserSetting;
use App\City;
use App\School;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\UploadedFile;
use Carbon\Carbon;
use DB;

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

            return $this->returnSuccess(__('User registration done successfully!'), $this->getDetails($create->id));
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

            return $this->returnSuccess(__('User personal details saved successfully!'), $this->getDetails($userId));
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
            return $this->returnSuccess(__('User school details saved successfully!'), $this->getDetails($user->id));
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

        if (!empty($data['birthday']) && strlen($data['birthday']) > 10) {
            $data['birthday'] = $data['birthday'] / 1000;
        }

        $user = $model::find($data['user_id']);

        $user->current_location = $data['current_location'];
        $user->nation           = $data['nation'];
        $user->gender           = $data['gender'];
        $user->birthday         = !empty($data['birthday']) ? Carbon::createFromTimestamp($data['birthday'])->toDateTime() : NULL;
        $user->current_status   = isset($data['current_status']) ? (int)$data['current_status'] : 0;
        $user->company          = !empty($data['company']) ? $data['company'] : NULL;
        $user->job_position     = !empty($data['job_position']) ? $data['job_position'] : NULL;
        $user->university       = !empty($data['university']) ? $data['university'] : NULL;
        $user->field_of_study   = !empty($data['field_of_study']) ? $data['field_of_study'] : NULL;
        $user->other_flag       = $model::OTHER_FLAG_DONE;
        $user->country_id       = !empty($data['country_id']) ? $data['country_id'] : NULL;
        $user->city_id          = !empty($data['city_id']) ? $data['city_id'] : NULL;

        if ($user->save()) {
            return $this->returnSuccess(__('User other details saved successfully!'), $this->getDetails($user->id));
        }

        return $this->returnError(__('Something went wrong!'));
    }

    public function registrationDocument(Request $request)
    {
        $data  = $request->all();
        $model = new UserDocument();

        $validator = $model->validator($data);

        if ($validator->fails()) {
            return $this->returnError($validator->errors()->first());
        }

        $userId = (int)$data['user_id'];

        $document = $data['document'];

        if ($document instanceof UploadedFile) {
            $pathInfos = pathinfo($document->getClientOriginalName());

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

                if (!empty($folder)) {
                    $fileName  = (empty($pathInfos['filename']) ? time() : $pathInfos['filename']) . '_' . time() . '.' . $pathInfos['extension'];
                    $storeFile = $document->storeAs($folder, $fileName, $model->fileSystem);

                    if ($storeFile) {
                        $save = $model->updateOrCreate(['document_type' => $data['document_type'], 'user_id' => $userId], ['document_type' => $data['document_type'], 'document' => $fileName, 'user_id' => $userId]);

                        if ($save) {
                            return $this->returnSuccess(__('User document saved successfully!'), $this->getDetails($userId));
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
            return $this->returnSuccess(__('Logged in successfully!'), $this->getDetails($user->id));
        }

        return $this->returnError(__('Username or Password is incorrect.'));
    }

    public function getDetails(int $userId, $isApi = false)
    {
        $model = new User();

        if (empty($userId) || !is_numeric($userId)) {
            return $this->returnError(__('User id seems incorrect.'));
        }

        $user = $model::with('userDocuments')->find($userId);

        if (!empty($user)) {
            if ($isApi) {
                return $this->returnSuccess(__('User details get successfully!'), $user);
            }

            return $user;
        }

        if ($isApi) {
            return $this->returnNull();
        }

        return $user;
    }

    public function getStatus(Request $request)
    {
        $model = new User();
        $data  = $request->all();

        if (empty($data['user_id']) || !is_numeric($data['user_id'])) {
            return $this->returnError(__('User id seems incorrect.'));
        }

        $userId = (int)$data['user_id'];

        $user = $model::select('id', 'personal_flag', 'school_flag', 'other_flag')->with('userDocuments')->find($userId);

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
                return $this->returnSuccess(__('User profile details updated successfully!'), $this->getDetails($user->id));
            }
        }

        return $this->returnNull();
    }

    /*public function forgotPassword(Request $request)
    {
        $model = new User();
        $data  = $request->all();
        $email = $request->get('email', false);

        if ($email) {
            $user = $model::where('email', $email)->first();

            if (!empty($user)) {
                $subject = 'Reset password for ' . env('APP_NAME', 'UONDA');

                $return = $this->sendMail('forgotPassword', $email, $subject, $user);

                dd($return);
            } else {
                return $this->returnError(__('Given email doesn\'t exists!'));
            }
        }

        return $this->returnError(__('Please provide email!'));
    }*/

    public function getExplore(Request $request)
    {
        $model       = new User();
        $schoolModel = new School();
        $data        = $request->all();

        // Check proper latitude & longitude
        $latitude = false;
        if (!empty($data['latitude']) && preg_match('/^[-]?(([0-8]?[0-9])\.(\d+))|(90(\.0+)?)$/', $data['latitude'])) {
            $latitude = $data['latitude'];
        }

        $longitude = false;
        if (!empty($data['longitude']) && preg_match('/^[-]?((((1[0-7][0-9])|([0-9]?[0-9]))\.(\d+))|180(\.0+)?)$/', $data['longitude'])) {
            $longitude = $data['longitude'];
        }

        $query            = $model::query();
        $selectStatements = $model->getTableName() . '.*';

        // 1609 for convert to miles.
        $distance  = (int)(defined('EXPLORE_DISTANCE') ? EXPLORE_DISTANCE : 500) / 1609;

        if ($latitude && $longitude) {
            $selectStatements = "
                {$model->getTableName()}.*, SQRT(
                POW(69.1 * (latitude - {$latitude}), 2) +
                POW(69.1 * ({$longitude} - longitude) * COS(latitude / 57.3), 2)) AS miles
            ";

            $query->having('miles', '<=', $distance);
        }

        /*$schoolName = $request->get('school_name', false);
        if (!empty($schoolName)) {
            $query->join($schoolModel::getTableName(), $model->getTableName() . '.school_id', '=', $schoolModel::getTableName() . '.id');
            $query->where($schoolModel::getTableName() . '.name', 'LIKE', '%' . $schoolName . '%');
        }*/

        $schoolId = $request->get('school_id', false);
        if (!empty($schoolId)) {
            $query->where($model->getTableName() . '.school_id', $schoolId);
        }

        $fieldOfStudy = $request->get('field_of_study', false);
        if (!empty($fieldOfStudy)) {
            $query->where($model->getTableName() . '.field_of_study', 'LIKE', '%' . $fieldOfStudy . '%');
        }

        $jobPosition = $request->get('job_position', false);
        if (!empty($jobPosition)) {
            $query->where($model->getTableName() . '.job_position', 'LIKE', '%' . $jobPosition . '%');
        }

        $company = $request->get('company', false);
        if (!empty($company)) {
            $query->where($model->getTableName() . '.company', 'LIKE', '%' . $company . '%');
        }

        $university = $request->get('university', false);
        if (!empty($university)) {
            $query->where($model->getTableName() . '.university', 'LIKE', '%' . $university . '%');
        }

        $records = $query->selectRaw($selectStatements)->get();

        if (!empty($records) && !$records->isEmpty()) {
            return $this->returnSuccess(__('Users found successfully!'), $records);
        }

        return $this->returnNull();
    }

    public function updateLocation(Request $request)
    {
        $model = new User();
        $data  = $request->all();

        $requiredFileds = [
            'name'      => ['nullable'],
            'password'  => ['nullable'],
            'email'     => ['nullable'],
            'latitude'  => ['required'],
            'longitude' => ['required']
        ];

        $extraFields = [
            'user_id' => ['required', 'integer', 'exists:' . $model->getTableName() . ',id']
        ];

        $validator = $model->validator($data, $requiredFileds, $extraFields);

        if ($validator->fails()) {
            return $this->returnError($validator->errors()->first());
        }

        $user = $model::find($data['user_id']);

        $user->latitude  = $data['latitude'];
        $user->longitude = $data['longitude'];

        if ($user->save()) {
            return $this->returnSuccess(__('User locations saved successfully!'), $this->getDetails($user->id));
        }

        return $this->returnError(__('Something went wrong!'));
    }
}
