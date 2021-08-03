<?php

namespace App\Http\Controllers;

use App\Email;
use App\Image;
use Illuminate\Support\Facades\Mail;
use View;
use Carbon\Carbon;
use GuzzleHttp\Client;
use App\AppText;

class BaseController extends Controller
{
    public $errorCode     = 401;
    public $blockCode     = 402;
    public $userIdCode    = 403;
    public $successCode   = 200;
    public $returnNullMsg = NO_RESPONSE;

    public function index()
    {
        dd('Welcome to ' . env('APP_NAME'));
    }

    public function returnError($message = NULL, $code = NULL)
    {
        $code = empty($code) ? $this->errorCode : $code;

        return response()->json([
            'code' => $code,
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
                'msg'  => __(PROVIDE_EMAIL_VIEW)
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
                'msg'  => __(EMAIL_NOT_SENT)
            ]);
        } else {
            if (!is_array($to)) {
                $to = [$to];
            }

            foreach ($to as $mailId) {
                Email::store([$toName . ' ' . $mailId], $subject, $bodyContent, $cc, $bcc, json_encode($attachments));
            }

            return response()->json([
                'code' => 200,
                'msg'  => __(EMAIL_SENT)
            ]);
        }
    }

    public function getImages()
    {
        $count = request()->get('count', 50);

        return Image::limit($count)->get();
    }

    public function callSelfApiGet(string $route, string $apiKey, array $param = [])
    {
        if (empty($route)) {
            return false;
        }

        $client  = new Client(['headers' => ['api-key' => $apiKey], 'verify' => !env('APP_DEBUG', false)]);

        $request = $client->get($route, ['json' => $param]);

        if ($request->getStatusCode() == $this->successCode) {
            return json_decode($request->getBody(), true);
        } else {
            return json_decode($request->getBody(), true);
        }
    }
}
