<?php

namespace App\Http\Controllers\User;

use App\User;
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

    public function verify(Request $request)
    {
        $receiptData = $request->get('receipt_data', null);
        $productName = $request->get('product_id', null);

        try {
            $response = $this->validator
                             ->setSharedSecret(config('services.ios.shared_secret'))
                             ->setReceiptData($receiptData)
                             ->validate();

            $receipt = $response->getRawData();
        } catch (Exception $e) {}

        if ($response->isValid()) {
            $latestReceipt = $receipt['receipt'] ?? [];

            if ($latestReceipt && $latestReceipt['product_id'] == $productName) {
                return $latestReceipt;
            }
        }

        return false;
    }

    public function store(Request $request)
    {
        $userId      = $request->get('user_id', null);
        $receiptData = $request->get('receipt_data', null);
        $productName = $request->get('product_id', null);

        // Check user exists.
        $user = User::find($userId);

        if (empty($user)) {
            return response()->json([
                'code'   => $this->errorCode,
                'msg'    => __(USER_NOT_FOUND),
                'status' => 0,
                'data'   => []
            ]);
        }

        // Check existing user for receipt data.
        $exists = User::where('receipt_data', $receiptData)->where('id', '!=', $userId)->exists();

        if ($exists) {
            return response()->json([
                'code'   => $this->errorCode,
                'msg'    => __(USER_PURCHASE_ALREADY_EXISTS),
                'status' => 0,
                'data'   => []
            ]);
        }

        // Check with in app purchase.
        $check = $this->verify($request);

        if (!$check) {
            return response()->json([
                'code'   => $this->errorCode,
                'msg'    => __(USER_UNVERIFY_PURCHASE),
                'status' => 0,
                'data'   => []
            ]);
        }

        // Set purchase info in user model.
        $update = $user->update(['payment_flag' => User::PAYMENT_FLAG_DONE, 'receipt_data' => $receiptData, 'product_id' => $productName]);

        if ($update) {
            return response()->json([
                'code'   => $this->successCode,
                'msg'    => __(USER_VERIFY_PURCHASE),
                'status' => 1,
                'data'   => $check
            ]);
        }

        return response()->json([
            'code'   => $this->errorCode,
            'msg'    => __(SOMETHING_WENT_WRONG),
            'status' => 0,
            'data'   => []
        ]);
    }
}
