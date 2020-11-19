<?php

namespace App\Http\Controllers;

use App\Email;
use Illuminate\Support\Facades\Mail;
use View;
use Carbon\Carbon;

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

    public function sendMail($view, $to, $subject, $body, $toName = '', $cc = '', $bcc = '', $attachments = [])
    {
        if (empty($view)) {
            return response()->json([
                'code' => 401,
                'msg'  => __('Please provide email view.')
            ]);
        }

        $validator = Email::validator(['to' => $to, 'subject' => $subject, 'body' => $body]);
        if ($validator->fails()) {
            return response()->json([
                'code' => 401,
                'msg'  => $validator->errors()->first()
            ]);
        }

        $bodyContent = View::make('emails.'. $view, compact('body'))->render();
        Mail::send('emails.'. $view, compact('body'), function($message) use ($to, $subject, $toName, $cc, $bcc, $attachments) {
            $message->to($to, $toName)
                    ->subject($subject);
            if (!empty($cc)) {
                $message->cc($cc);
            }

            if (!empty($bcc)) {
                $message->bcc($bcc);
            }

            if (!empty($attachments)) {
                foreach ($attachments as $attachment) {
                    if (empty($attachment['path'])) {
                        continue;
                    }

                    $as   = (!empty($attachment['as'])) ? $attachment['as'] : '';
                    $mime = (!empty($attachment['mime'])) ? $attachment['mime'] : '';

                    $message->attach(public_path('storage/' . $attachment['path']), ['as' => $as, 'mime' => $mime]);
                }
            }
        });

        if (Mail::failures()) {
            return response()->json([
                'code' => 401,
                'msg'  => __('Email not sent')
            ]);
        } else {
            if (!is_array($to)) {
                $to = [$to];
            }

            foreach ($to as $mailId) {
                Email::insert([
                    'from'           => env('MAIL_FROM_ADDRESS', ''),
                    'to'             => $toName . ' ' . $mailId,
                    'cc'             => $cc,
                    'bcc'            => $bcc,
                    'subject'        => $subject,
                    'body'           => $bodyContent,
                    'attachments'    => json_encode($attachments),
                    'exception_info' => NULL,
                    'created_at'     => Carbon::now()
                ]);
            }

            return response()->json([
                'code' => 200,
                'msg'  => __('Email sent successfully !')
            ]);
        }
    }
}
