<?php

namespace App\Services;

use App\Models\Notification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class RealtimeNotificationService
{
    public function send(Notification $notification): void
    {
        $url = config('services.node_notification.url');

        if (! $url) {
            return;
        }

        try {
            $response = Http::timeout((int) config('services.node_notification.timeout', 2))
                ->post($url, [
                    'user_id' => $notification->user_id,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'type' => $notification->type,
                    'data' => $notification->data ?? [],
                ]);

            if ($response->failed()) {
                Log::warning('Realtime notification request failed.', [
                    'notification_id' => $notification->id,
                    'status' => $response->status(),
                ]);
            }
        } catch (Throwable $exception) {
            Log::warning('Realtime notification service is unavailable.', [
                'notification_id' => $notification->id,
                'message' => $exception->getMessage(),
            ]);
        }
    }
}
