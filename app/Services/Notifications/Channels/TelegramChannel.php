<?php

namespace App\Services\Notifications\Channels;

use Illuminate\Support\Facades\Log;

class TelegramChannel implements NotificationChannelInterface
{
    public function send(string $to, string $message, array $options = []): bool
    {
        Log::info("Sending Telegram Alert to: {$to}. Message: {$message}");
        // In a real scenario, use Telegram Bot API
        return true;
    }
}
