<?php

namespace App\Http\Controllers\User\Auth;

use App\Http\Controllers\BaseController;
use Illuminate\Support\Facades\Password;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;
use APp\User;

class ForgotPasswordController extends BaseController
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    use SendsPasswordResetEmails;

    protected function sendResetLinkResponse(Request $request, $response)
    {
        $user  = [];
        $model = new User();
        $email = $request->get('email', false);

        if ($email) {
            $user = $model::with('userDocuments')->where('email', $email)->first();
        }

        return $this->returnSuccess(trans($response), $user);
    }

    protected function sendResetLinkFailedResponse(Request $request, $response)
    {
        return $this->returnError(trans($response));
    }
}
