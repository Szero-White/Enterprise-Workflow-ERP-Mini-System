<?php

namespace App\Jobs;

use App\Models\Notification;
use App\Services\RealtimeNotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendRealtimeNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 10;

    public function __construct(public int $notificationId)
    {
        $this->onQueue('notifications');
    }

    public function handle(RealtimeNotificationService $realtimeNotificationService): void
    {
        $notification = Notification::find($this->notificationId);

        if (! $notification) {
            return;
        }

        $realtimeNotificationService->send($notification);
    }
}
