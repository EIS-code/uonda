<?php

namespace App\Jobs;

use App\User;
use App\Notification as modalNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UserRejectNotification extends BaseNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(int $userId, array $dataPayload)
    {
        request()->merge(['user_id' => User::ADMIN_ID]);

        request()->merge(['request_user_id' => $userId]);

        parent::__construct(__('You are rejected by Admin.'), NULL, $dataPayload);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        request()->merge(['show_rejected' => true]);

        $this->send();

        request()->merge(['show_rejected' => false]);
    }
}
