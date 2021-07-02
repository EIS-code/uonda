<?php

namespace App\Jobs;

use App\Notification as modalNotification;
use App\User;
use App\Feed;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreateFeedNotification extends BaseNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $feedId;

    protected $feedTitle;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(int $feedId)
    {
        request()->merge(['user_id' => User::ADMIN_ID]);

        $this->feedId  = $feedId;

        $dataPayload    = ['notification_type' => modalNotification::NOTIFICATION_FEED];

        parent::__construct(__('New Feed Created'), $this->getDescription(), $dataPayload, false, true);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if (empty($this->feedTitle)) {
            return false;
        }

        $this->send();
    }

    public function getDescription()
    {
        $feed = Feed::find($this->feedId);

        return $this->feedTitle = !empty($feed) ? $feed->title : NULL;
    }
}
