<?php

namespace App\Services\Notifications\Channels;

use Illuminate\Support\Facades\Log;

class ZaloChannel implements NotificationChannelInterface
{
    public function send(string $to, string $message, array $options = []): bool
    {
        Log::info("Sending Zalo Alert to: {$to}. Message: {$message}");
        // In a real scenario, use Zalo OA API
        return true;
    }
}
