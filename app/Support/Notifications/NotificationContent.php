<?php

namespace App\Support\Notifications;

final readonly class NotificationContent
{
    public function __construct(
        public string $title,
        public string $message,
        public array $data = [],
    ) {}
}
