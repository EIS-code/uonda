<?php

namespace App\Console\Commands;

use App\User;
use App\Http\Controllers\User\iOSReceipt;
use ReceiptValidator\iTunes\Validator as iOSValidator;
use Illuminate\Console\Command;

class iOSVerifyAppPurchase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ios:app:purchase:scheduler';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'For verify each user\'s iOS in app purchase.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $request = request();

        $ctliOSReceipt = new iOSReceipt(new iOSValidator());

        $verify = [];

        $users = User::where('is_enable', User::IS_ENABLED)->where('is_accepted', User::IS_ACCEPTED)->where('is_admin', User::IS_USER)->whereRaw("LOWER(device_type) = '". strtolower(User::DEVICE_TYPE_IOS) . "'")->where('payment_flag', User::PAYMENT_FLAG_DONE);

        $users->chunk(50, function($records) use($request, $ctliOSReceipt, &$verify) {
            foreach ($records as $record) {
                $request->merge(['receipt_data' => $record->receipt_data, 'product_id' => $record->product_id]);

                $verify[$record->id] = $ctliOSReceipt->verify($request);
            }
        });

        dd($verify);

        return 0;
    }
}
