<?php

namespace App\Http\Controllers\User\Auth;

use App\Http\Controllers\BaseController;
use Illuminate\Support\Facades\Password;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;
use App\User;
use App\Email;

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
            Email::store([$email], __(PASSWORD_RESET_LINK_SENT), trans($response));

            $user = $model::with('userDocuments')->where('email', $email)->first();
        }

        return $this->returnSuccess(trans($response), $user);
    }

    protected function sendResetLinkFailedResponse(Request $request, $response)
    {
        $email = $request->get('email', false);

        Email::store([$email], __(RESET_LINK_EXCEPTION), trans($response), [], [], NULL, Email::IS_NOT_SEND, trans($response));

        return $this->returnError(trans($response));
    }
}
