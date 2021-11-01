<?php

namespace App\Http\Middleware;

use App\Repositories\ApiKeyRepository;
use Closure;
use App\ApiKey;

class AuthApi
{
    private $excludedRoutes = [
        'api/user/login',
        // 'api/user/details/other',
        'api/user/registration/school',
        'api/user/registration/other',
        'api/user/registration/personal',
        'api/user/registration/status',
        'api/user/registration/document',
        'api/user/registration/documents',
        'api/location/get/country',
        'api/location/get/state',
        'api/location/get/city',
        'api/school/get',
        'api/school/save',
        'api/school/update',
        'api/user/details',
        'api/user/forgot/password',
        'api/user/password/reset',
        'api/user/chat/send',
        'api/user/chat/notification/message/send',
        'api/user/chat/notification/message/group/send',
        'api/constant/user/terms_and_conditions'
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $apiKey = (!empty($request->header('api-key'))) ? $request->header('api-key') : false;

        if (in_array($request->route()->uri, $this->excludedRoutes)) {
            $request->merge(['show_rejected' => true]);

            return $next($request);
        }

        $getKeyInfo = $this->validate($apiKey);

        if (!$apiKey || empty($getKeyInfo)) {
            return response()->json([
                'code' => 401,
                'msg'  => 'API key is missing or wrong.'
            ]);
        }

        if ($this->totalLogins($getKeyInfo->user_id)) {
            return response()->json([
                'code' => 401,
                'msg'  => 'You can\'t login on multiple device.'
            ]);
        }

        // $getKeyInfo = $getKeyInfo->first();

        /*if (!$request->has('user_id')) {
            $request->merge(['is_own' => true]);
        } else {
            $request->merge(['is_own' => false]);
        }*/

        if ($request->has('user_id')) {
            $request->merge(['request_user_id' => $request->get('user_id')]);
        }

        $request->merge(['user_id' => $getKeyInfo->user_id]);

        return $next($request);
    }

    private function validate(string $key)
    {
        $getKeyInfo = ApiKey::where('key', $key)->where('is_valid', '1')->first();

        return $getKeyInfo;
    }

    private function totalLogins(int $userId)
    {
        $getUsers = ApiKey::where('user_id', $userId)->where('is_valid', '1')->count();

        return ($getUsers > config('app.allowed_api_user_logins'));
    }
}
