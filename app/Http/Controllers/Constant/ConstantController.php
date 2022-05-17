<?php

namespace App\Http\Controllers\Constant;

use App\Http\Controllers\BaseController;
use App\Constant;

class ConstantController extends BaseController
{
    public function termsAndConditions()
    {
        if (defined('TERMS_AND_CONDITIONS')) {
            return $this->returnSuccess(__(TERMS_AND_CONDITIONS_ADDED), TERMS_AND_CONDITIONS);
        }

        return $this->returnNull();
    }

    public function aboutUs()
    {
        if (defined('ABOUT_US')) {
            return $this->returnSuccess(__(ABOUT_US_FOUND), ABOUT_US);
        }

        return $this->returnNull();
    }

    public function appPrivacyPolicy()
    {
        if (defined('APP_PRIVACY_POLICY')) {
            return $this->returnSuccess(__(APP_PRIVACY_POLICY_FOUND), APP_PRIVACY_POLICY);
        }

        return $this->returnNull();
    }
}
