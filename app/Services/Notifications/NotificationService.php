<?php

namespace App\Services\Notifications;

use App\Services\Notifications\Channels\NotificationChannelInterface;
use App\Services\Notifications\Channels\TelegramChannel;
use App\Services\Notifications\Channels\ZaloChannel;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    protected array $channels = [];

    public function __construct()
    {
        // Register default channels
        $this->channels['telegram'] = new TelegramChannel();
        $this->channels['zalo'] = new ZaloChannel();
    }

    /**
     * Send notification to specific channel or all channels.
     */
    public function notify(string $message, array $targets = []): void
    {
        foreach ($targets as $channelType => $to) {
            if (isset($this->channels[$channelType])) {
                $this->channels[$channelType]->send($to, $message);
            }
        }
    }

    /**
     * Broadcast alert based on severity.
     */
    public function broadcastAlert(string $alertType, string $content, string $severity = 'INFO'): void
    {
        $message = "[{$severity}] ALERT: {$alertType} - {$content}";

        // Example: Only send critical alerts to all configured channels
        if ($severity === 'CRITICAL') {
            Log::warning("Broadcasting CRITICAL Alert: {$message}");
            // logic to fetch tenant notification settings...
        }
    }
}
