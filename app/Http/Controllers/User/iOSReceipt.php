<?php

namespace App\Http\Controllers\User;

use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use App\Http\Controllers\BaseController;
use ReceiptValidator\iTunes\Validator as iOSValidator;

class iOSReceipt extends BaseController
{
    protected $validator;

    public function __construct(iOSValidator $iOSValidator)
    {
        $this->validator = $iOSValidator;

        // Or iTunesValidator::ENDPOINT_SANDBOX if sandbox testing
        if (config('app.env') == 'local') {
            $this->validator->setEndpoint(iOSValidator::ENDPOINT_SANDBOX);
        }
    }

    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $receiptData = $request->get('receipt_data', null);
        $productName = $request->get('product_id', null);

        try {
            $response = $this->validator
                             ->setSharedSecret(config('services.ios.shared_secret'))
                             ->setReceiptData($receiptData)
                             ->validate();

            $receipt = $response->getRawData();
        } catch (Exception $e) {
            return response()->json([
                'code'   => $this->errorCode,
                'msg'    => __("Something went wrong! Please try again after an hour."),
                'status' => 0
            ]);
        }

        if ($response->isValid()) {
            $latestReceipt = $receipt['receipt'] ?? [];

            if ($latestReceipt && $latestReceipt['product_id'] == $productName) {
                return response()->json([
                    'code'   => $this->successCode,
                    'msg'    => __(USER_VERIFY_PURCHASE),
                    'status' => 1,
                    'data'   => $latestReceipt
                ]);
            }
        }

        return response()->json([
            'code'   => $this->errorCode,
            'msg'    => __(USER_UNVERIFY_PURCHASE),
            'status' => 0
        ]);
    }
}
