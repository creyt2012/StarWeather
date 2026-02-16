<?php

namespace App\Services\Notifications\Channels;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SlackChannel implements NotificationChannelInterface
{
    /**
     * Send notification via Slack Webhook.
     */
    public function send(string $to, string $message, array $options = []): bool
    {
        try {
            Http::post($to, ['text' => $message]);
            return true;
        } catch (\Exception $e) {
            Log::error("Slack notification error: " . $e->getMessage());
            return false;
        }
    }
}
