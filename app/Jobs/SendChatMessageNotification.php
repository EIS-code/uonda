<?php

namespace App\Jobs;

use App\Notification as modalNotification;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendChatMessageNotification extends BaseNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $message;

    protected $notificationTitle;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(int $userId, string $message = NULL, int $fromUserId = NULL, $dataPayload)
    {
        request()->merge(['user_id' => $fromUserId]);

        request()->merge(['request_user_id' => $userId]);

        $this->message              = truncate($message, 20);

        $fromUser                   = User::find($fromUserId);

        $this->notificationTitle    = MASSAGE_RECEIVED_FROM;

        $this->notificationTitle    = !empty($fromUser) ? __($this->notificationTitle . $fromUser->fullName) : __($this->notificationTitle);

        parent::__construct($this->notificationTitle, $this->message, $dataPayload);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->send();
    }
}
