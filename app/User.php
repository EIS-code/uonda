<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\School;
use App\Country;
use App\City;
use App\UserSetting;
use App\ApiKey;
use App\UserBlockProfile;
use App\Notification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Collection;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'user_name', 'sur_name', 'email', 'password', 'referral_code', 'current_location', 'nation', 'gender', 'birthday', 'short_bio', 'school_id', 'state_id', 'country_id', 'city_id', 'origin_country_id', 'origin_city_id',
        'current_status', 'company', 'job_position', 'university', 'field_of_study', 'profile', 'profile_icon', 'personal_flag', 'school_flag', 'other_flag', 'latitude', 'longitude', 'device_token', 'device_type', 'app_version', 'oauth_uid', 'oauth_provider', 'is_online'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token', 'personal_flag', 'school_flag', 'other_flag',
        // 'user_name',
        // 'email',
        'created_at', 'updated_at', 'oauth_uid', 'oauth_provider', 'notifications',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * The attributes that should be appends to model object.
     *
     * @var array
     */
    public $appends = ['encrypted_user_id', 'permissions', 'total_notifications', 'total_read_notifications', 'total_unread_notifications', 'school_name', 'is_blocked'];

    const MALE = 'm';
    const FEMALE = 'f';

    public $genders = [
        self::MALE => 'Male',
        self::FEMALE => 'Female'
    ];

    const PERSONAL_FLAG_DONE = '1';
    const PERSONAL_FLAG_PENDING = '0';

    const SCHOOL_FLAG_DONE = '1';
    const SCHOOL_FLAG_PENDING = '0';

    const OTHER_FLAG_DONE = '1';
    const OTHER_FLAG_PENDING = '0';

    public $personalFlags = [
        self::PERSONAL_FLAG_DONE => 'Done',
        self::PERSONAL_FLAG_PENDING => 'Pending'
    ];

    public $schoolFlags = [
        self::SCHOOL_FLAG_DONE => 'Done',
        self::SCHOOL_FLAG_PENDING => 'Pending'
    ];

    public $otherFlags = [
        self::OTHER_FLAG_DONE => 'Done',
        self::OTHER_FLAG_PENDING => 'Pending'
    ];

    public $allowedProfileExtensions = ['jpg', 'jpeg', 'png', 'gif'];
    public $fileSystem               = 'public';
    public $profile                  = 'user\\profile';
    public $profileIcon              = 'user\\profile\\icons';

    const OAUTH_NONE        = '0';
    const OAUTH_GOOGLE      = '1';
    const OAUTH_FACEBOOK    = '2';
    const OAUTH_APPLE       = '3';
    const OAUTH_INSTAGRAM   = '4';

    public $oauthProviders = [
        self::OAUTH_NONE        => 'None',
        self::OAUTH_GOOGLE      => 'Google',
        self::OAUTH_FACEBOOK    => 'Facebook',
        self::OAUTH_APPLE       => 'Apple',
        self::OAUTH_INSTAGRAM   => 'Instagram'
    ];

    const IS_ADMIN = '1';
    const IS_USER = '0';

    const ADMIN_ID = '1';

    const IS_ONLINE     = '1';
    const IS_NOT_ONLINE = '0';

    const ADMIN_DEVICE_TOKEN = 'admin';

    const DEVICE_TYPE_IOS       = 'ios';
    const DEVICE_TYPE_ANDROID   = 'android';

    const IS_BLOCKED = '1';
    const IS_NOT_BLOCKED = '0';

    const IS_PENDING  = '0';
    const IS_ACCEPTED = '1';
    const IS_REJECTED = '2';

    public function __construct(array $attributes = array())
    {
        parent::__construct($attributes);

        // Hidden fields.
        if (request()->has('user_id') && request()->get('user_id', false)) {
            $userId = (int)request()->get('user_id');

            $getSettings = UserSetting::where('user_id', $userId)->first();

            if (!empty($getSettings)) {
                if (in_array('user_name', $this->hidden) && $getSettings->user_name == UserSetting::CONSTS_PUBLIC) {
                    $key = array_search($getSettings->user_name, $this->hidden);

                    $this->makeVisible('user_name');
                }

                if (in_array('email', $this->hidden) && $getSettings->email == UserSetting::CONSTS_PUBLIC) {
                    $key = array_search($getSettings->email, $this->hidden);

                    $this->makeVisible('email');
                }
            }
        }
    }

    public function getTableName()
    {
        return with(new static)->getTable();
    }

    public function appendNewFields($fields)
    {
        if (is_array($fields)) {
            array_merge($fields, $this->appends);
        } elseif (is_string($fields)) {
            array_push($this->appends, $fields);
        }
    }

    public function validator(array $data, $requiredFileds = [], $extraFields = [], $returnBoolsOnly = false)
    {
        $validator = Validator::make($data, array_merge([
            'name'             => array_merge(['string', 'max:255'], !empty($requiredFileds['name']) ? $requiredFileds['name'] : ['required']),
            'user_name'        => array_merge(['string', 'max:255'], !empty($requiredFileds['user_name']) ? $requiredFileds['user_name'] : ['nullable']),
            'sur_name'        => array_merge(['string', 'max:255'], !empty($requiredFileds['sur_name']) ? $requiredFileds['sur_name'] : ['nullable']),
            'password'         => array_merge(['min:6'], !empty($requiredFileds['password']) ? $requiredFileds['password'] : ['nullable']),
            'email'            => array_merge(['email', 'unique:' . $this->getTableName()], !empty($requiredFileds['email']) ? $requiredFileds['email'] : ['nullable']),
            'referral_code'    => array_merge(['string', 'max:255'], !empty($requiredFileds['referral_code']) ? $requiredFileds['referral_code'] : ['nullable']),
            'current_location' => array_merge(['string'], !empty($requiredFileds['current_location']) ? $requiredFileds['current_location'] : ['nullable']),
            'nation'           => array_merge(['string', 'max:255'], !empty($requiredFileds['nation']) ? $requiredFileds['nation'] : ['nullable']),
            'gender'           => array_merge(['in:' . implode(",", array_keys($this->genders))], !empty($requiredFileds['gender']) ? $requiredFileds['gender'] : ['nullable']),
            'birthday'         => array_merge([], !empty($requiredFileds['string']) ? $requiredFileds['string'] : ['nullable']),
            'short_bio'        => array_merge(['string'], !empty($requiredFileds['short_bio']) ? $requiredFileds['short_bio'] : ['nullable']),
            'school_id'        => array_merge(['integer', 'exists:' . School::getTableName() . ',id'], !empty($requiredFileds['school_id']) ? $requiredFileds['school_id'] : ['nullable']),
            'country_id'       => array_merge(['integer', 'exists:' . Country::getTableName() . ',id'], !empty($requiredFileds['country_id']) ? $requiredFileds['country_id'] : ['nullable']),
            'origin_country_id'       => array_merge(['integer', 'exists:' . Country::getTableName() . ',id'], !empty($requiredFileds['origin_country_id']) ? $requiredFileds['origin_country_id'] : ['nullable']),
            'state_id'         => array_merge(['integer', 'exists:' . State::getTableName() . ',id'], !empty($requiredFileds['state_id']) ? $requiredFileds['state_id'] : ['nullable']),
            'city_id'          => array_merge(['integer', 'exists:' . City::getTableName() . ',id'], !empty($requiredFileds['city_id']) ? $requiredFileds['city_id'] : ['nullable']),
            'origin_city_id'          => array_merge(['integer', 'exists:' . City::getTableName() . ',id'], !empty($requiredFileds['origin_city_id']) ? $requiredFileds['origin_city_id'] : ['nullable']),
            'current_status'   => array_merge(['nullable', 'in:0,1,2,3'], !empty($requiredFileds['current_status']) ? $requiredFileds['current_status'] : ['nullable']),
            'company'          => array_merge(['string', 'max:255'], !empty($requiredFileds['company']) ? $requiredFileds['company'] : ['nullable']),
            'job_position'     => array_merge(['string', 'max:255'], !empty($requiredFileds['job_position']) ? $requiredFileds['job_position'] : ['nullable']),
            'university'       => array_merge(['string', 'max:255'], !empty($requiredFileds['university']) ? $requiredFileds['university'] : ['nullable']),
            'field_of_study'   => array_merge(['string', 'max:255'], !empty($requiredFileds['field_of_study']) ? $requiredFileds['field_of_study'] : ['nullable']),
            'profile'          => array_merge(['mimes:' . implode(",", $this->allowedProfileExtensions)], !empty($requiredFileds['profile']) ? $requiredFileds['profile'] : ['nullable']),
            'profile_icon'     => array_merge(['mimes:' . implode(",", $this->allowedProfileExtensions)], !empty($requiredFileds['profile_icon']) ? $requiredFileds['profile_icon'] : ['nullable']),
            'personal_flag'    => array_merge(['nullable', 'in:' . implode(",", array_keys($this->personalFlags))], !empty($requiredFileds['personal_flag']) ? $requiredFileds['personal_flag'] : ['nullable']),
            'school_flag'      => array_merge(['nullable', 'in:' . implode(",", array_keys($this->schoolFlags))], !empty($requiredFileds['school_flag']) ? $requiredFileds['school_flag'] : ['nullable']),
            'other_flag'       => array_merge(['nullable', 'in:' . implode(",", array_keys($this->otherFlags))], !empty($requiredFileds['other_flag']) ? $requiredFileds['other_flag'] : ['nullable']),
            'latitude'        => array_merge(['nullable', 'between:0,99.99'], !empty($requiredFileds['latitude']) ? $requiredFileds['latitude'] : ['nullable']),
            'longitude'       => array_merge(['nullable', 'between:0,99.99'], !empty($requiredFileds['longitude']) ? $requiredFileds['longitude'] : ['nullable']),
            'device_token'    => array_merge(['string'], !empty($requiredFileds['device_token']) ? $requiredFileds['device_token'] : ['nullable']),
            'device_type'     => array_merge(['string'], !empty($requiredFileds['device_type']) ? $requiredFileds['device_type'] : ['nullable']),
            'app_version'     => array_merge(['string'], !empty($requiredFileds['app_version']) ? $requiredFileds['app_version'] : ['nullable']),
            'oauth_uid'       => array_merge(['string', 'unique:' . $this->getTableName() . ',oauth_uid'], !empty($requiredFileds['oauth_uid']) ? $requiredFileds['oauth_uid'] : ['nullable']),
            'oauth_provider'  => array_merge(['in:' . implode(",", array_keys($this->oauthProviders))], !empty($requiredFileds['oauth_provider']) ? $requiredFileds['oauth_provider'] : ['nullable']),
            'is_online'       => array_merge(['string', 'unique:' . $this->getTableName() . ',is_online'], !empty($requiredFileds['is_online']) ? $requiredFileds['is_online'] : ['nullable']),
            'socket_id'       => array_merge(['string', 'unique:' . $this->getTableName() . ',socket_id'], !empty($requiredFileds['socket_id']) ? $requiredFileds['socket_id'] : ['nullable']),
        ], $extraFields));

        if ($returnBoolsOnly === true) {
            if ($validator->fails()) {
                \Session::flash('error', $validator->errors()->first());
            }

            return !$validator->fails();
        }

        return $validator;
    }

    public function country()
    {
        return $this->hasOne('App\Country', 'id', 'country_id');
    }

    public function state()
    {
        return $this->hasOne('App\State', 'id', 'state_id');
    }

    public function city()
    {
        return $this->hasOne('App\City', 'id', 'city_id');
    }

    public function originCountry()
    {
        return $this->hasOne('App\Country', 'id', 'origin_country_id');
    }

    public function originCity()
    {
        return $this->hasOne('App\City', 'id', 'origin_city_id');
    }

    public function school()
    {
        return $this->hasOne('App\School', 'id', 'school_id');
    }

    //To set the birthday in date format
    public function setBirthdayAttribute($value)
    {
        $this->attributes['birthday'] = Carbon::createFromTimestampMs($value);
    }

    public function getGenderAttribute($value)
    {
        return !empty($this->genders[$value]) ? $this->genders[$value] : $value;
    }

    public function getFullNameAttribute($value)
    {
        return $this->name . ' ' . $this->sur_name;
    }

    public function getBirthdayAttribute($value)
    {
        if (empty($value)) {
            return $value;
        }

        return strtotime($value) * 1000;
    }

    public function getIsBlockedAttribute()
    {
        $userId        = (int)request()->get('user_id', false);
        $requestUserId = $this->id;

        return (int)$this->isBlocked($userId, $requestUserId);
    }

    public function userDocuments()
    {
        return $this->hasMany('App\UserDocument', 'user_id', 'id');
    }

    public function userPermission()
    {
        return $this->hasOne('App\UserSetting', 'user_id', 'id');
    }

    public function referralUsers()
    {
        return $this->hasMany('App\UserReferral', 'referral_user_id', 'id')->with('user');
    }
    
    public function notifications($isAll = false, $isRead = Notification::IS_UNREAD, $isSuccess = Notification::IS_SUCCESS)
    {
        if ($isAll) {
            return $this->hasMany('App\Notification', 'user_id', 'id');
        } else {
            return $this->hasMany('App\Notification', 'user_id', 'id')->where('is_read', $isRead)->where('is_success', $isSuccess);
        }
    }

    public function getTotalReadNotificationsAttribute()
    {
        return $this->notifications(false, Notification::IS_READ)->count();
    }

    public function getTotalNotificationsAttribute()
    {
        return $this->notifications(true)->count();
    }

    public function getTotalUnreadNotificationsAttribute()
    {
        return $this->notifications->count();
    }

    /**
     * likes of feed by current user.
     */
    public function likedFeeds()
    {
        return $this->belongsToMany(Feed::class, 'feed_likes')->withTimestamps();
    }

    public function ChatRoomsUsers() {
        return $this->belongsTo(ChatRoomUser::class, 'id', 'sender_id');
    }

    //get encrypted user id
    public function getEncryptedUserIdAttribute()
    {
        return encrypt($this->id);
    }

    public function getPermissionsAttribute()
    {
        if (request()->has('user_id') && request()->get('user_id', false)) {
            $userId = (int)request()->get('user_id');

            $getSettings = UserSetting::where('user_id', $userId)->first();

            if (!empty($getSettings)) {
                return $getSettings;
            }
        }

        return [];
    }

    public function getApiKeyAttribute()
    {
        return ApiKey::getApiKey($this->id);
    }

    public function getProfileAttribute($value)
    {
        if (empty($value)) {
            return $value;
        }

        $storageFolderName = (str_ireplace("\\", "/", $this->profile));
        return Storage::disk($this->fileSystem)->url($storageFolderName . '/' . $value);
    }

    public function getProfileIconAttribute($value)
    {
        if (empty($value)) {
            return $value;
        }

        $storageFolderName = (str_ireplace("\\", "/", $this->profileIcon));
        return Storage::disk($this->fileSystem)->url($storageFolderName . '/' . $value);
    }

    public function getCountryNameAttribute()
    {
        $country = $this->country;

        return !empty($country) ? $country->name : NULL;
    }

    public function getOriginCountryNameAttribute()
    {
        $originCountry = $this->originCountry;

        return !empty($originCountry) ? $originCountry->name : NULL;
    }

    public function getOriginCityNameAttribute()
    {
        $originCity = $this->originCity;

        return !empty($originCity) ? $originCity->name : NULL;
    }

    public function getStateNameAttribute()
    {
        $state = $this->state;

        return !empty($state) ? $state->name : NULL;
    }

    public function getCityNameAttribute()
    {
        $city = $this->city;

        return !empty($city) ? $city->name : NULL;
    }

    public function getSchoolNameAttribute()
    {
        if(!empty($this->school_id)) {
            return School::where('id', $this->school_id)->pluck('name')->first();
        } else if(!empty($this->school)) {
            return $this->school;
        }
        return NULL;
    }

    public function newQuery($excludeDeleted = true)
    {
        $userBlockProfilesModel = new UserBlockProfile();

        $userId = request()->get('user_id', false);
        $requestedUserId = request()->get('request_user_id', false);
        $showRejected = request()->get('show_rejected', false);

        if (!empty($userId)) {
            // Check is blocked first.
            if ($this->isBlocked($userId, $requestedUserId)) {
                /*return parent::newQuery($excludeDeleted)->leftJoin($userBlockProfilesModel::getTableName(), $this->getTableName() . '.id', '=', $userBlockProfilesModel::getTableName() . '.user_id')
                             ->where($userBlockProfilesModel::getTableName() . '.is_block', '1')
                             ->where($userBlockProfilesModel::getTableName() . '.blocked_by', $userId);*/

                return parent::newQuery($excludeDeleted)->whereRaw("{$this->getTableName()}.id not in (select `user_id` from {$userBlockProfilesModel::getTableName()} where `blocked_by` = {$userId} and `is_block` = '".$userBlockProfilesModel::IS_BLOCK."') and {$this->getTableName()}.id not in (select `blocked_by` from {$userBlockProfilesModel::getTableName()} where `user_id` = {$userId} and `is_block` = '".$userBlockProfilesModel::IS_BLOCK."')")->where('is_accepted', '!=', self::IS_REJECTED);
            }
        }

        if ($showRejected === true) {
            return parent::newQuery($excludeDeleted);
        }

        return parent::newQuery($excludeDeleted)->where('is_accepted', '!=', self::IS_REJECTED);
    }

    public function isBlocked(int $userId, int $requestedUserId)
    {
        if (empty($requestedUserId)) {
            return false;
        }

        $userBlockProfilesModel = new UserBlockProfile();

        $checkBlocked = $userBlockProfilesModel::where(
            function($query) use($userId, $requestedUserId) {
                $query->where('blocked_by', (int)$userId)
                      ->where('user_id', (int)$requestedUserId)
                      ->orWhere(function($qry) use($userId, $requestedUserId) {
                          $qry->where('blocked_by', (int)$requestedUserId)
                              ->where('user_id', (int)$userId);
                      });
            }
        )->where('is_block', (string)$userBlockProfilesModel::IS_BLOCK)->first();

        return !empty($checkBlocked);
    }

    public function isRejected(int $userId)
    {
        $user = $this->find($userId);

        return (!empty($user) && $user->is_accepted == self::IS_REJECTED);
    }

    public static function setDeviceInfos(array $data = [])
    {
        $userId = !empty($data['user_id']) ? (int)$data['user_id'] : false;

        if (empty($userId)) {
            return false;
        }

        $user = self::find($userId);

        if (empty($user)) {
            return false;
        }

        if (!empty($data['device_token'])) {
            $user->device_token = $data['device_token'];
        }

        if (!empty($data['device_type'])) {
            $user->device_type = $data['device_type'];
        }

        if (!empty($data['app_version'])) {
            $user->app_version = $data['app_version'];
        }

        return $user->save();
    }

    public static function getDeviceToken(int $id)
    {
        $user = self::find($id);

        if (!empty($user)) {
            return $user->device_token;
        }

        return NULL;
    }

    public static function getDeviceType(int $id)
    {
        $user = self::find($id);

        if (!empty($user)) {
            return strtolower($user->device_type);
        }

        return NULL;
    }

    public function isIOS()
    {
        return (strtolower($this->device_type) == self::DEVICE_TYPE_IOS);
    }

    public function isAndroid()
    {
        return (strtolower($this->device_type) == self::DEVICE_TYPE_ANDROID);
    }

    public function removeBlockedUsers(Collection $users)
    {
        if (!empty($users) && !$users->isEmpty()) {
            $users->each(function($user, $key) use($users) {
                if ($user->is_blocked == self::IS_BLOCKED) {
                    unset($users[$key]);
                }
            });
        }

        return $users;
    }

    public static function setPendingUser(int $userId)
    {
        $find = self::find($userId);

        if (!empty($find)) {
            $find->is_accepted = self::IS_PENDING;

            return $find->save();
        }

        return false;
    }
}
