<?php

namespace App\Helper;

use App\User;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use ReceiptValidator\iTunes\Validator as iOSValidator;
use Carbon\Carbon;

class iOSReceiptHelper
{
    protected $errorCode   = 401;
    protected $successCode = 200;

    protected $validator;

    public function __construct(iOSValidator $iOSValidator)
    {
        $this->validator = $iOSValidator;

        // Or iTunesValidator::ENDPOINT_SANDBOX if sandbox testing
        if (config('app.env') == 'local') {
            $this->validator->setEndpoint(iOSValidator::ENDPOINT_SANDBOX);
        }
    }

    public function verify($receiptData)
    {
        $return = false;

        if (empty($receiptData)) {
            return $return;
        }

        try {
            $response = $this->validator
                             ->setSharedSecret(config('services.ios.shared_secret'))
                             ->setReceiptData($receiptData)
                             ->validate();

            $receipt = $response->getRawData();
        } catch (Exception $e) {}

        if ($response->isValid()) {
            $now = Carbon::now();

            foreach ($response->getPurchases() as $purchase) {
                if (empty($purchase->getExpiresDate())) {
                    continue;
                }

                // Check cancelled date if available it means receipt cancelled by user.
                if (!empty($purchase->getCancellationDate())) {
                    continue;
                }

                // Check expires date.
                if ($purchase->getExpiresDate()->gte($now)) {
                    $return = $purchase;

                    break;
                }
            }
        }

        return $return;
    }

    public function store(Request $request)
    {
        $userId      = $request->get('user_id', null);
        $receiptData = $request->get('receipt_data', null);

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
        $check = $this->verify($receiptData);

        if (!$check) {
            User::setPaymentFlagPending($userId);

            return response()->json([
                'code'   => $this->errorCode,
                'msg'    => __(USER_UNVERIFY_PURCHASE),
                'status' => 0
            ]);
        }

        $productId     = !empty($check['product_id']) ? $check['product_id'] : null;
        $transactionId = !empty($check['original_transaction_id']) ? $check['original_transaction_id'] : null;

        // Check existing user for transaction id.
        $exists = User::where('transaction_id', $transactionId)->where('id', '!=', $userId)->exists();

        if ($exists) {
            return response()->json([
                'code'   => $this->errorCode,
                'msg'    => __(USER_PURCHASE_ALREADY_EXISTS),
                'status' => 0
            ]);
        }

        // Set purchase info in user model.
        $update = User::setPaymentFlagDone($userId, $receiptData, $productId, $transactionId);

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

    public function check(Request $request, int $userId)
    {
        // Check user exists.
        $user = !empty($userId) ? User::find($userId) : null;

        if (empty($user)) {
            return response()->json([
                'code'   => $this->errorCode,
                'msg'    => __(USER_NOT_FOUND)
            ]);
        }

        if (empty($user->receipt_data)) {
            User::setPaymentFlagPending($userId);

            return response()->json([
                'code'   => $this->errorCode,
                'msg'    => __(USER_UNVERIFY_PURCHASE)
            ]);
        }

        // Check with in app purchase.
        $check = $this->verify($user->receipt_data);

        if (!$check) {
            User::setPaymentFlagPending($userId);

            return response()->json([
                'code'   => $this->errorCode,
                'msg'    => __(USER_UNVERIFY_PURCHASE)
            ]);
        }

        $purchaseDate = Carbon::createFromTimestampUTC((int)round($check['purchase_date_ms']) / 1000)->timezone(config('app.local_timezone'));
        $expiresDate  = Carbon::createFromTimestampUTC((int)round($check['expires_date_ms']) / 1000)->timezone(config('app.local_timezone'));

        return response()->json([
            'code'   => $this->successCode,
            'msg'    => __(USER_VERIFY_PURCHASE),
            'data'   => ['purchase_date' => $purchaseDate->format('Y-m-d H:i:s T'), 'expires_date' => $expiresDate->format('Y-m-d H:i:s T')]
        ]);
    }
}
