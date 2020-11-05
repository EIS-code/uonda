<?php

namespace App\Http\Controllers;

class BaseController extends Controller
{
    public $errorCode     = 401;
    public $successCode   = 200;
    public $returnNullMsg = 'No response found!';

    public function index()
    {
        dd('Welcome to ' . env('APP_NAME'));
    }

    public function returnError($message = NULL)
    {
        return response()->json([
            'code' => $this->errorCode,
            'msg'  => $message
        ]);
    }

    public function returnSuccess($message = NULL, $with = NULL)
    {
        return response()->json([
            'code' => $this->successCode,
            'msg'  => $message,
            'data' => $with
        ]);
    }

    public function returnNull($message = NULL)
    {
        return response()->json([
            'code' => $this->successCode,
            'msg'  => !empty($message) ? $message : $this->returnNullMsg
        ]);
    }
}
