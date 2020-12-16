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
        'api/user/registration/document'
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
