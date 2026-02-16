<?php

namespace App\Services\Notifications\Channels;

use Illuminate\Support\Facades\Log;

class WebPushChannel implements NotificationChannelInterface
{
    /**
     * Send Web Push notification to user device.
     */
    public function send(string $to, string $message, array $options = []): bool
    {
        // Simulation of Web Push VAPID protocol
        Log::info("SIMULATING WEB PUSH: Sending to endpoint {$to}. Content: {$message}");
        return true;
    }
}
