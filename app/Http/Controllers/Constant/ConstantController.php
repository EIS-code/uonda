<?php

namespace App\Http\Controllers\Constant;

use App\Http\Controllers\BaseController;
use App\Constant;

class ConstantController extends BaseController
{
    public function termsAndConditions()
    {
        if (defined('TERMS_AND_CONDITIONS')) {
            return $this->returnSuccess(__('Terms and conditions found successfully!'), TERMS_AND_CONDITIONS);
        }

        return $this->returnNull();
    }

    public function aboutUs()
    {
        if (defined('ABOUT_US')) {
            return $this->returnSuccess(__('About Us found successfully!'), ABOUT_US);
        }

        return $this->returnNull();
    }
}
