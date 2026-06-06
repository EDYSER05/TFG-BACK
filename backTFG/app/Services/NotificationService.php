<?php

namespace App\Services;

use App\Models\Notification;

class NotificationService
{
    public static function send(int $userId, string $message, string $type = 'general'): void
    {
        Notification::create([
            'user_id' => $userId,
            'message' => $message,
            'type'    => $type,
            'is_read' => false,
        ]);
    }
}
