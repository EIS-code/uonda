<?php

namespace App\Jobs;

use App\Notification as modalNotification;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ScreenshotNotification extends BaseNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        $dataPayload = ['notification_type' => modalNotification::NOTIFICATION_SCREENSHOT_CAPTURED];

        parent::__construct(__(SCRREN_SHOT_TAKE), NULL, $dataPayload, true);
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
