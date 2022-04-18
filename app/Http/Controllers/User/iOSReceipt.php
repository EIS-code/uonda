<?php

namespace App\Http\Controllers\User;

use App\User;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use App\Http\Controllers\BaseController;
use ReceiptValidator\iTunes\Validator as iOSValidator;
use Carbon\Carbon;

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
        $return      = false;

        $receiptData = $request->get('receipt_data', null);

        try {
            $response = $this->validator
                             ->setSharedSecret(config('services.ios.shared_secret'))
                             ->setReceiptData($receiptData)
                             ->validate();

            $receipt = $response->getRawData();
        } catch (Exception $e) {}

        if ($response->isValid()) {
            $now = Carbon::now()->timezone('Etc/GMT');

            foreach ($response->getPurchases() as $purchase) {
                if (empty($purchase->getExpiresDate())) {
                    continue;
                }

                // Check expires date.
                if ($purchase->getExpiresDate()->gte($now)) {
                    $return = $purchase;
                }
            }
            /* $latestReceipt = $receipt['receipt'] ?? [];

            if (!empty($latestReceipt['in_app'])) {
                $now = Carbon::now()->timezone('Etc/GMT');

                foreach ($latestReceipt['in_app'] as $inApp) {
                    if (empty($inApp['purchase_date_ms'])) {
                        continue;
                    }

                    $expiresDate = Carbon::parse($inApp['expires_date']);

                    if ($expiresDate->gte($now)) {
                        $return = $inApp;
                    }
                }
            } */
        }

        return $return;
    }

    public function store(Request $request)
    {
        $userId      = $request->get('user_id', null);
        $receiptData = $request->get('receipt_data', null);

        \Log::info("receiptData : " . $receiptData);

        // Check user exists.
        $user = User::find($userId);

        if (empty($user)) {
            return response()->json([
                'code'   => $this->errorCode,
                'msg'    => __(USER_NOT_FOUND),
                'status' => 0
            ]);
        }

        // Check existing user for receipt data.
        $exists = User::where('receipt_data', $receiptData)->where('id', '!=', $userId)->exists();

        if ($exists) {
            return response()->json([
                'code'   => $this->errorCode,
                'msg'    => __(USER_PURCHASE_ALREADY_EXISTS),
                'status' => 0
            ]);
        }

        // Check with in app purchase.
        $check = $this->verify($request);

        if (!$check) {
            return response()->json([
                'code'   => $this->errorCode,
                'msg'    => __(USER_UNVERIFY_PURCHASE),
                'status' => 0
            ]);
        }

        $productId = !empty($check['product_id']) ? $check['product_id'] : null;

        // Set purchase info in user model.
        $update = $user->update(['payment_flag' => User::PAYMENT_FLAG_DONE, 'receipt_data' => $receiptData, 'product_id' => $productId]);

        if ($update) {
            return response()->json([
                'code'   => $this->successCode,
                'msg'    => __(USER_VERIFY_PURCHASE),
                'status' => 1
            ]);
        }

        return response()->json([
            'code'   => $this->errorCode,
            'msg'    => __(SOMETHING_WENT_WRONG),
            'status' => 0
        ]);
    }
}
