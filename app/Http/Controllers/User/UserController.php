<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use App\User;
use App\UserDocument;
use App\UserSetting;
use App\City;
use App\School;
use App\Country;
use App\ApiKey;
use App\UserBlockProfile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\UploadedFile;
use Carbon\Carbon;
use DB;
use Intervention\Image\ImageManagerStatic as Image;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\UserReferral;

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
                            $fileName  = removeSpaces($fileName);
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
                            $fileName  = removeSpaces($fileName);
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
                            $fileName  = removeSpaces($fileName);
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
                            $fileName  = removeSpaces($fileName);
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
    
    public function returns($message = NULL, $with = NULL, $isError = false)
    {
        if ($isError && !empty($message)) {
            $message = !empty($this->errorMsg[$message]) ? __($this->errorMsg[$message]) : __($message);
        } else {
            $message = !empty($this->successMsg[$message]) ? __($this->successMsg[$message]) : __($this->returnNullMsg);
        }

        if (!$isError && !empty($with)) {
            if ($with instanceof Collection && !$with->isEmpty()) {
                return $this->returnSuccess($message, array_values($with->toArray()));
            } else {
                return $this->returnSuccess($message, $with->toArray());
            }
        } elseif ($isError) {
            return $this->returnError($message);
        }

        return $this->returnNull();
    }

    public function getReferralCode() {
        $code = Str::random(6);
        $check = User::where('referral_code', $code)->first();
        if (empty($check)) {
            return $code;
        } else {
            return $this->getReferralCode();
        }
    }
    
    public function addUserReferral(Request $request, $userId){
        $user = User::where('referral_code', $request->referral_code)->first();
        if(empty($user)) {
            return ['isError' => true, 'message' => 'Referral code not found!'];
        }
        $data = [
            'user_id' => $userId,
            'referral_user_id' => $user->id,
            'referral_code' => $request->referral_code
        ];
        $model = new UserReferral();
        
        $validator = $model->validator($data);
        if ($validator->fails()) {
            return ['isError' => true, 'message' => $validator->errors()->first()];
        }
        $model->create($data);
        return ['isError' => false, 'message' => 'successfully added'];
    }

    public function registrationPersonal(Request $request)
    {
        DB::beginTransaction();
        try {
            $data  = $request->all();
            $referral_code = $this->getReferralCode();
            $model = new User();

            $data['personal_flag'] = $model::PERSONAL_FLAG_DONE;
            $data['referral_code'] = $referral_code;

            $data['oauth_provider'] = !empty($data['oauth_provider']) ? (string)$data['oauth_provider'] : $model::OAUTH_NONE;

            $validator = $model->validator($data);

            if ($validator->fails()) {
                return $this->returnError($validator->errors()->first());
            }

            $data['password'] = !empty($data['password']) ? Hash::make($data['password']) : NULL;

            $create = $model->create($data);

            if (!empty($data['profile']) && $data['profile'] instanceof UploadedFile) {
                $profile   = $data['profile'];
                $pathInfos = pathinfo($profile->getClientOriginalName());

                if (!empty($pathInfos['extension'])) {
                    $folder     = $model->profile;
                    $folderIcon = $model->profileIcon;

                    if (!empty($folder)) {
                        $fileName  = (empty($pathInfos['filename']) ? time() : $pathInfos['filename']) . '_' . time() . '.' . $pathInfos['extension'];
                        $fileName  = removeSpaces($fileName);
                        $storeFile = $profile->storeAs($folder, $fileName, $model->fileSystem);

                        if ($storeFile) {
                            // Set 100 x 100 px icon for later use for example in Chats.
                            $profileIcon = Image::make($profile)->fit(100)->encode($pathInfos['extension']);

                            if ($profileIcon) {
                                $iconName  = time() . '.png';
                                $storeIcon = Storage::disk($model->fileSystem)->put($folderIcon . '\\' . $iconName, $profileIcon->__toString());

                                if ($storeIcon) {
                                    $create->update(['profile_icon' => $iconName]);
                                }
                            }
                            $create->update(['profile' => $fileName]);
                        }
                    }
                }
            }

            if ($create) {
                $userId = $create->id;

                // Privacy
                UserSetting::create(['user_id' => $userId, 'user_name' => UserSetting::CONSTS_PRIVATE, 'email' => UserSetting::CONSTS_PRIVATE, 'notification' => UserSetting::NOTIFICATION_ON]);

                if (isset($request->referral_code) && !empty($request->referral_code)) {
                    $addUserReferral = $this->addUserReferral($request, $userId);

                    if (!empty($addUserReferral['isError']) && !empty($addUserReferral['message'])) {
                        return $this->returns($addUserReferral['message'], NULL, true);
                    }
                }

                DB::commit();

                return $this->returnSuccess(__('User personal details saved successfully!'), $this->getDetails($userId, false, true));
            }
            return $this->returnError(__('Something went wrong!'));
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        } catch (\Throwable $e) {
            DB::rollback();
            throw $e;
        }
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
            'current_location' => ['nullable'],
            'nation'           => ['nullable'],
            'gender'           => ['required'],
            'birthday'         => ['nullable'],
            'country_id'       => ['nullable'],
            'state_id'         => ['nullable'],
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

        // $user->current_location = $data['current_location'];
        // $user->nation           = $data['nation'];
        $user->gender           = $data['gender'];
        $user->birthday         = !empty($data['birthday']) ? $data['birthday'] : NULL;
        $user->current_status   = isset($data['current_status']) ? ''.$data['current_status'] : '0';
        $user->company          = !empty($data['company']) ? $data['company'] : NULL;
        $user->job_position     = !empty($data['job_position']) ? $data['job_position'] : NULL;
        $user->university       = !empty($data['university']) ? $data['university'] : NULL;
        $user->field_of_study   = !empty($data['field_of_study']) ? $data['field_of_study'] : NULL;
        $user->other_flag       = $model::OTHER_FLAG_DONE;
        // $user->country_id       = !empty($data['country_id']) ? $data['country_id'] : NULL;
        // $user->state_id         = !empty($data['state_id']) ? $data['state_id'] : NULL;
        // $user->city_id          = !empty($data['city_id']) ? $data['city_id'] : NULL;
        if ($user->save()) {
            $data = $this->getDetails($user->id);
            $data->api_key = ApiKey::generateKey($user->id);
            return $this->returnSuccess(__('User other details saved successfully!'), $data);
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
                    $fileName  = removeSpaces($fileName);
                    $storeFile = $document->storeAs($folder, $fileName, $model->fileSystem);

                    if ($storeFile) {
                        $save = $model->updateOrCreate(['document_type' => $data['document_type'], 'user_id' => $userId], ['document_type' => $data['document_type'], 'document' => $fileName, 'user_id' => $userId]);

                        if ($save) {
                            User::setPendingUser($userId);

                            return $this->returnSuccess(__('User document saved successfully!'), $this->getDetails($userId));
                        }
                    }
                }
            }
        }

        return $this->returnError(__('Something went wrong!'));
    }

    public function registrationDocuments(Request $request)
    {
        $data  = $request->all();
        $model = new UserDocument();

        $userId        = !empty($data['user_id']) ? (int)$data['user_id'] : false;
        $documents     = !empty($data['document']) ? (array)$data['document'] : [];
        $documentTypes = (!empty($documents)) ? array_keys($documents) : [];
        $save          = false;

        $validators = $model->validators(['document_types' => $documentTypes, 'documents' => $documents, 'user_id' => $userId]);

        if ($validators->fails()) {
            return $this->returnError($validators->errors()->first());
        }

        foreach ($documents as $documentType => $document) {
            $documentType = (string)$documentType;

            if ($document instanceof UploadedFile) {
                $pathInfos = pathinfo($document->getClientOriginalName());

                if (!empty($pathInfos['extension'])) {
                    $folder = false;

                    if ($documentType == $model::GRADUATION_CERTIFICATE) {
                        $folder = $model->graduation;
                    } elseif ($documentType == $model::STUDENT_ID_CARD) {
                        $folder = $model->studentIdCard;
                    } elseif ($documentType == $model::PHOTO_IN_UNIFORM) {
                        $folder = $model->photoInUniform;
                    } elseif ($documentType == $model::CLASS_PHOTO) {
                        $folder = $model->classPhoto;
                    }

                    if (!empty($folder)) {
                        $fileName  = (empty($pathInfos['filename']) ? time() : $pathInfos['filename']) . '_' . time() . '.' . $pathInfos['extension'];
                        $fileName  = removeSpaces($fileName);
                        $storeFile = $document->storeAs($folder, $fileName, $model->fileSystem);

                        if ($storeFile) {
                            $save = $model->updateOrCreate(['document_type' => $documentType, 'user_id' => $userId], ['document_type' => $documentType, 'document' => $fileName, 'user_id' => $userId]);
                        }
                    }
                }
            }
        }

        if ($save) {
            User::setPendingUser($userId);

            return $this->returnSuccess(__('User documents saved successfully!'), $this->getDetails($userId));
        }

        return $this->returnError(__('Something went wrong!'));
    }

    public function doLogin(Request $request)
    {
        $request->merge(['show_rejected' => true]);

        $model = new User();
        $data  = $request->all();

        $oauthId  = !empty($data['oauth_uid']) ? $data['oauth_uid'] : NULL;
        $userName = !empty($data['username']) ? $data['username'] : NULL;
        $password = !empty($data['password']) ? $data['password'] : NULL;

        // Check username & password.
        $if     = ((empty($userName) || empty($password)) && empty($oauthId));
        $elseif = (empty($oauthId) && (empty($userName) || empty($password)));

        if ($if) {
            return $this->returnError(__('Username or Password is incorrect.'));
        } elseif ($elseif) {
            return $this->returnError(__('Oauth uid is incorrect.'));
        }

        $isUserNamePasswordLogin = (!empty($userName) && !empty($password) || empty($oauthId));
        $isOauthLogin            = (!$isUserNamePasswordLogin && (empty($userName) || empty($password)) && !empty($oauthId));

        if ($isUserNamePasswordLogin) {
            $user = $model->where('email', $userName)->first();
        } elseif ($isOauthLogin) {
            $user = $model->where('oauth_uid', $oauthId)->first();
        }

        $check = false;

        if (!empty($user)) {
            if ($isUserNamePasswordLogin) {
                $check = ((string)$user->email === (string)$userName && Hash::check($password, $user->password));
            } elseif ($isOauthLogin) {
                $check = (string)$user->oauth_uid === (string)$oauthId;
            }
        }

        if ($check === true) {
            // Generate API key.
            ApiKey::generateKey($user->id);

            // Set device informations if request having.
            $data['user_id'] = $user->id;
            $model::setDeviceInfos($data);

            $request->merge(['show_rejected' => false]);

            return $this->returnSuccess(__('Logged in successfully!'), $this->getDetails($user->id, false, true));
        } elseif ($isOauthLogin) {
            $this->errorCode = 402;

            $request->merge(['show_rejected' => false]);

            return $this->returnError(__('OauthId is incorrect.'));
        }

        $request->merge(['show_rejected' => false]);

        return $this->returnError(__('Username or Password is incorrect.'));
    }

    public function getDetails(int $userId, $isApi = false, $apiKey = false)
    {
        $model = new User();
        $requestedUserId = request()->get('user_id', false);

        if (empty($userId) || !is_numeric($userId)) {
            return $this->returnError(__('User id seems incorrect.'));
        }

        if ($model->isBlocked($requestedUserId, $userId)) {
            return $this->returnError(__('This profile blocked by user.'), $this->blockCode);
        }

        $user = $model::with(['userDocuments','userPermission'])->find($userId);
        
        if (!empty($user)) {
            if ($apiKey) {
                // Generate API key.
                ApiKey::generateKey($userId);

                array_push($user->appends, 'api_key');
            }

            array_push($user->appends, 'country_name');
            array_push($user->appends, 'state_name');
            array_push($user->appends, 'city_name');
            array_push($user->appends, 'school_name');
            array_push($user->appends, 'origin_country_name');
            array_push($user->appends, 'origin_city_name');

            if ($isApi) {
                // Set device informations if request having.
                $model::setDeviceInfos(request()->all());

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

        $userId     = (int)$data['user_id'];

        $getUser    = function() use($userId, $model) {
            return $model::select('id', 'device_token', 'personal_flag', 'school_flag', 'other_flag' , 'origin_country_id' , 'is_accepted', 'reason_for_rejection')->with('userDocuments')->find($userId);
        };

        $user   = $getUser();

        if (empty($user)) {
            return $this->returnError(__('User id seems incorrect.'));
        }

        if (!empty($user)) {
            // Set device informations if request having.
            $data['user_id'] = $user->id;
            $model::setDeviceInfos($data);

            $user = $getUser();

            $user->makeVisible(['personal_flag', 'school_flag', 'other_flag', 'origin_country_id' , 'is_accepted']);

            // $user->makeHidden(['notifications']);

            $user->api_key = ApiKey::generateKey($user->id);

            return $this->returnSuccess(__('User details get successfully!'), $user);
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
                $msg = NULL;
                $modelDocument = new UserDocument();

                if (isset($data['document_type']) && array_key_exists($data['document_type'], $modelDocument->documentTypes)) {
                    $document = $data['document'];

                    if ($document instanceof UploadedFile) {
                        $pathInfos = pathinfo($document->getClientOriginalName());

                        if (!empty($pathInfos['extension'])) {
                            $folder = false;

                            if ($data['document_type'] == $modelDocument::GRADUATION_CERTIFICATE) {
                                $folder = $modelDocument->graduation;
                            } elseif ($data['document_type'] == $modelDocument::STUDENT_ID_CARD) {
                                $folder = $modelDocument->studentIdCard;
                            } elseif ($data['document_type'] == $modelDocument::PHOTO_IN_UNIFORM) {
                                $folder = $modelDocument->photoInUniform;
                            } elseif ($data['document_type'] == $modelDocument::CLASS_PHOTO) {
                                $folder = $modelDocument->classPhoto;
                            }

                            if (!empty($folder)) {
                                $fileName  = (empty($pathInfos['filename']) ? time() : $pathInfos['filename']) . '_' . time() . '.' . $pathInfos['extension'];
                                $fileName  = removeSpaces($fileName);
                                $storeFile = $document->storeAs($folder, $fileName, $modelDocument->fileSystem);

                                if ($storeFile) {
                                    $save = $modelDocument->updateOrCreate(['document_type' => $data['document_type'], 'user_id' => $userId], ['document_type' => $data['document_type'], 'document' => $fileName, 'user_id' => $userId]);

                                    if ($save) {
                                        $msg = __(' with user document');
                                    }
                                }
                            }
                        }
                    }
                }

                if (!empty($data['profile']) && $data['profile'] instanceof UploadedFile) {
                    $profile   = $data['profile'];
                    $pathInfos = pathinfo($profile->getClientOriginalName());

                    if (!empty($pathInfos['extension'])) {
                        $folder     = $model->profile;
                        $folderIcon = $model->profileIcon;

                        if (!empty($folder)) {
                            $fileName  = (empty($pathInfos['filename']) ? time() : $pathInfos['filename']) . '_' . time() . '.' . $pathInfos['extension'];
                            $fileName  = removeSpaces($fileName);
                            $storeFile = $profile->storeAs($folder, $fileName, $model->fileSystem);

                            if ($storeFile) {
                                // Set 100 x 100 px icon for later use for example in Chats.
                                $profileIcon = Image::make($profile)->fit(100)->encode($pathInfos['extension']);

                                if ($profileIcon) {
                                    $iconName  = time() . '.png';
                                    $storeIcon = Storage::disk($model->fileSystem)->put($folderIcon . '\\' . $iconName, $profileIcon->__toString());

                                    if ($storeIcon) {
                                        $user->update(['profile_icon' => $iconName]);
                                    }
                                }

                                $user->update(['profile' => $fileName]);
                            }
                        }
                    }
                }

                return $this->returnSuccess(__('User profile details updated successfully') . $msg . '!', $this->getDetails($user->id));
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
        $cityModel   = new City();
        $userBlockProfilesModel = new UserBlockProfile();
        $data        = $request->all();

        $userId = !empty($data['user_id']) ? (int)$data['user_id'] : false;

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
        $selectStatements = "{$model->getTableName()}.id, {$model->getTableName()}.name, {$model->getTableName()}.user_name, {$model->getTableName()}.profile, {$schoolModel::getTableName()}.name as school, {$model->getTableName()}.latitude, {$model->getTableName()}.longitude, {$model->getTableName()}.current_location, {$cityModel::getTableName()}.name as city, {$model->getTableName()}.job_position, {$model->getTableName()}.company , {$model->getTableName()}.university, {$model->getTableName()}.is_online";

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

        $type = $request->get('type', false);
        if (!empty($type)) {
            $keyword = $request->get('keyword', false);

            // if (!empty($keyword)) {}
            switch($type) {
                case "school":
                    $latitude = $longitude = false;

                    $query->where($schoolModel::getTableName() . '.name', 'LIKE', '%' . $keyword . '%');
                    break;
                case "location":
                    $latitude = $longitude = false;
                    break;
                case 'job_position':
                    $latitude = $longitude = false;

                    $query->where($model->getTableName() . '.job_position', 'LIKE', '%' . $keyword . '%');
                    break;
                case 'company':
                    $latitude = $longitude = false;

                    $query->where($model->getTableName() . '.company', 'LIKE', '%' . $keyword . '%');
                    break;
                case 'university':
                    $latitude = $longitude = false;

                    $query->where($model->getTableName() . '.university', 'LIKE', '%' . $keyword . '%');
                    break;
                case "person":
                    if ($type == "person") {
                        $latitude = $longitude = false;
                    }

                    $query->where(function($query) use($model, $keyword) {
                        $query->where($model->getTableName() . '.name', 'LIKE', '%' . $keyword . '%')
                              ->orWhere($model->getTableName() . '.email', 'LIKE', '%' . $keyword . '%')
                              ->orWhere($model->getTableName() . '.user_name', 'LIKE', '%' . $keyword . '%');
                    });
                    break;
            }
        }

        // 1609 for convert to miles.
        $distance  = (int)(defined('EXPLORE_DISTANCE') ? EXPLORE_DISTANCE : 500) / 1609;

        if ($latitude && $longitude) {
            $selectStatements .= "
                , SQRT(
                POW(69.1 * ({$cityModel::getTableName()}.latitude - {$latitude}), 2) +
                POW(69.1 * ({$longitude} - {$cityModel::getTableName()}.longitude) * COS({$cityModel::getTableName()}.latitude / 57.3), 2)) AS miles
            ";

            $query->having('miles', '<=', $distance);
        }

        $query->join($schoolModel::getTableName(), $model->getTableName() . '.school_id', '=', $schoolModel::getTableName() . '.id');
        $query->leftJoin($userBlockProfilesModel::getTableName(), function($leftJoin) use($model, $userBlockProfilesModel, $userId) {
            $leftJoin->on($model->getTableName() . '.id', '=', $userBlockProfilesModel::getTableName() . '.user_id')
                     ->where($userBlockProfilesModel::getTableName() . '.is_block', (string)$userBlockProfilesModel::IS_BLOCK)
                     ->where(function($where) use($model, $userBlockProfilesModel, $userId) {
                        $where->where($userBlockProfilesModel::getTableName() . '.user_id', '=', $userId)
                              ->orWhere($userBlockProfilesModel::getTableName() . '.blocked_by', '=', $userId);
                     });
        });
        $query->leftJoin($cityModel::getTableName(), $model->getTableName() . '.city_id', '=', $cityModel::getTableName() . '.id');

        $query->whereNull($userBlockProfilesModel::getTableName() . '.id');

        $query->where($model->getTableName() . '.id', '!=', $userId);

        $query->where($model->getTableName() . '.id', '!=', $model::IS_ADMIN);

        $query->where($model->getTableName() . '.is_accepted', '!=', $model::IS_REJECTED);

        $records = $query->selectRaw($selectStatements)->get();

        if (!empty($records) && !$records->isEmpty()) {
            $records->makeHidden(['permissions', 'encrypted_user_id']);

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

    public function removeDocument(Request $request)
    {
        $model  = new UserDocument();
        $data   = $request->all();
        $userId = !empty($data['user_id']) ? (int)$data['user_id'] : false;
        $id     = !empty($data['id']) ? (int)$data['id']: false;

        if (empty($userId)) {
            return $this->returnError(__('User id seems incorrect.'));
        }

        if (empty($id)) {
            return $this->returnError(__('Document id seems incorrect.'));
        }

        $userDocument = $model::where('id', $id)->where('user_id', $userId)->limit(1)->delete();

        if ($userDocument) {
            return $this->returnSuccess(__('User document removed successfully!'));
        }

        return $this->returnError(__('Something went wrong!'));
    }

    //Function to save the location of user
    public function saveOriginLocation(Request $request) {
        $model = new User();
        $data  = $request->all();
        
        if (empty($data['user_id']) || !is_numeric($data['user_id'])) {
            return $this->returnError(__('User id seems incorrect.'));
        }

        $userId = (int)$data['user_id'];
        unset($data['user_id']);

        $user = $model::find($userId);
        $data['name'] = $user->name;
        if (!empty($user)) {
            $fillableFields = $model->getFillable();

            $requiredFileds = [
                'country_id'      => ['required'],
                'city_id'  => ['required'],
                'origin_country_id'      => ['required'],
                'origin_city_id'  => ['required'],
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
        }
        if ($user->save()) {
            $msg = NULL;
            $data = $this->getDetails($user->id);
            $data->api_key = ApiKey::getApiKey($user->id);
            return $this->returnSuccess(__('User location details updated successfully') . $msg . '!', $data);
        }
        return $this->returnNull();
    }

    //Function to logout the user
    public function logoutUser(Request $request) {
        if(!empty($request->user_id)) {
            $model = ApiKey::where('user_id', $request->user_id)->delete();
            return $this->returnSuccess(__('You are successfully logged out!'));
        }
        return $this->returnError(__('Something went wrong!'));
    }

    public function getUserExplore(Request $request)
    {
        $per_page = $request->has('per_page') ? $request->per_page : 10;
        $offset = $request->has('offset') ? (int)$request->offset : 0;
        $status = 200;
        $next_offset = $offset + $per_page;
        $model       = new User();
        $schoolModel = new School();
        $cityModel   = new City();
        $countryModel   = new Country();
        $userBlockProfilesModel = new UserBlockProfile();
        $data        = $request->all();

        $userId = !empty($data['user_id']) ? (int)$data['user_id'] : false;

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
        $selectStatements = "{$model->getTableName()}.id, {$model->getTableName()}.name, {$model->getTableName()}.user_name, {$model->getTableName()}.profile, {$schoolModel::getTableName()}.name as school, {$model->getTableName()}.latitude, {$model->getTableName()}.longitude, {$model->getTableName()}.current_location, {$cityModel::getTableName()}.name as city,{$countryModel::getTableName()}.name as country, {$model->getTableName()}.job_position, {$model->getTableName()}.company , {$model->getTableName()}.university, {$model->getTableName()}.is_online";

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

        $type = $request->get('type', false);
        if (!empty($type)) {
            $keyword = $request->get('keyword', false);

            // if (!empty($keyword)) {}
            switch($type) {
                case "school":
                    $latitude = $longitude = false;

                    $query->where($schoolModel::getTableName() . '.name', 'LIKE', '%' . $keyword . '%');
                    break;
                case "country":
                    $latitude = $longitude = false;
                    $query->where($countryModel::getTableName() . '.name', 'LIKE', $keyword . '%');
                    break;
                case "city":
                    $latitude = $longitude = false;
                    $query->where($cityModel::getTableName() . '.name', 'LIKE', $keyword . '%');
                    break;
                case "location":
                    $latitude = $longitude = false;
                    $query->where($cityModel::getTableName() . '.name', 'LIKE', '%' . $keyword . '%');
                    break;
                case 'job_position':
                    $latitude = $longitude = false;

                    $query->where($model->getTableName() . '.job_position', 'LIKE', '%' . $keyword . '%');
                    break;
                case 'company':
                    $latitude = $longitude = false;

                    $query->where($model->getTableName() . '.company', 'LIKE', '%' . $keyword . '%');
                    break;
                case 'university':
                    $latitude = $longitude = false;

                    $query->where($model->getTableName() . '.university', 'LIKE', '%' . $keyword . '%');
                    break;
                case "person":
                    if ($type == "person") {
                        $latitude = $longitude = false;
                    }

                    $query->where(function($query) use($model, $keyword) {
                        $query->where($model->getTableName() . '.name', 'LIKE', '%' . $keyword . '%')
                              ->orWhere($model->getTableName() . '.email', 'LIKE', '%' . $keyword . '%')
                              ->orWhere($model->getTableName() . '.user_name', 'LIKE', '%' . $keyword . '%');
                    });
                    break;
            }
        }

        // 1609 for convert to miles.
        $distance  = (int)(defined('EXPLORE_DISTANCE') ? EXPLORE_DISTANCE : 500) / 1609;

        if ($latitude && $longitude) {
            $selectStatements .= "
                , SQRT(
                POW(69.1 * ({$cityModel::getTableName()}.latitude - {$latitude}), 2) +
                POW(69.1 * ({$longitude} - {$cityModel::getTableName()}.longitude) * COS({$cityModel::getTableName()}.latitude / 57.3), 2)) AS miles
            ";

            $query->having('miles', '<=', $distance);
        }

        $query->join($schoolModel::getTableName(), $model->getTableName() . '.school_id', '=', $schoolModel::getTableName() . '.id');
        $query->leftJoin($userBlockProfilesModel::getTableName(), function($leftJoin) use($model, $userBlockProfilesModel, $userId) {
            $leftJoin->on($model->getTableName() . '.id', '=', $userBlockProfilesModel::getTableName() . '.user_id')
                     ->where($userBlockProfilesModel::getTableName() . '.is_block', (string)$userBlockProfilesModel::IS_BLOCK)
                     ->where(function($where) use($model, $userBlockProfilesModel, $userId) {
                        $where->where($userBlockProfilesModel::getTableName() . '.user_id', '=', $userId)
                              ->orWhere($userBlockProfilesModel::getTableName() . '.blocked_by', '=', $userId);
                     });
        });
        $query->leftJoin($cityModel::getTableName(), $model->getTableName() . '.city_id', '=', $cityModel::getTableName() . '.id');
        $query->leftJoin($countryModel::getTableName(), $model->getTableName() . '.country_id', '=', $countryModel::getTableName() . '.id');

        $query->whereNull($userBlockProfilesModel::getTableName() . '.id');

        $query->where($model->getTableName() . '.id', '!=', $userId);

        $query->where($model->getTableName() . '.id', '!=', $model::IS_ADMIN);

        $query->where($model->getTableName() . '.is_accepted', '!=', $model::IS_REJECTED);

        $records = $query->selectRaw($selectStatements)->skip($offset)->take($per_page)->get();

        $total_counts = $records->count();

        if ($next_offset >= $total_counts) {
            $next_offset = $offset;
        }

        if (!empty($records) && !$records->isEmpty()) {
            $records->makeHidden(['permissions', 'encrypted_user_id', 'notifications']);

            return response()->json([
                'code' => $status,
                'msg'  => __('Users found successfully!'),
                'current_offset' => $offset,
                'next_offset' => $next_offset,
                'per_page' => $per_page,
                'total_records' => $total_counts,
                'data' => $records
            ], 200);
        }

        return $this->returnNull();
    }
    
    //Function to get all the documents 
    public function getAllDocuments(Request $request) {
        $data = $request->all();
        $userId = !empty($data['user_id']) ? (int)$data['user_id'] : false;

        if (!empty($userId)) {
            $documents = UserDocument::where('user_id', $userId)->get();
            return $this->returnSuccess(__('User documents are successfully fetched!'), $documents);
        }
        return $this->returnError(__('Something went wrong!'));
    }
}
