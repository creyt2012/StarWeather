<?php

namespace App\Services\Notifications;

use App\Services\Notifications\Channels\NotificationChannelInterface;
use App\Services\Notifications\Channels\TelegramChannel;
use App\Services\Notifications\Channels\ZaloChannel;
use App\Services\Notifications\Channels\SlackChannel;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    protected array $channels = [];

    public function __construct()
    {
        // Register default channels
        $this->channels['telegram'] = new TelegramChannel();
        $this->channels['zalo'] = new ZaloChannel();
        $this->channels['slack'] = new SlackChannel();
        $this->channels['web_push'] = new Channels\WebPushChannel();
    }

    /**
     * Send notification to specific channel or all channels.
     */
    public function notify(string $message, array $targets = []): void
    {
        foreach ($targets as $channelType => $to) {
            if (isset($this->channels[$channelType])) {
                $this->channels[$channelType]->send($to, $message, []);
            }
        }
    }

    /**
     * Broadcast alert based on severity.
     */
    public function broadcastAlert(string $alertType, string $content, string $severity = 'INFO'): void
    {
        $message = "[{$severity}] ALERT: {$alertType} - {$content}";

        // In a real multi-tenant app, we'd loop through tenants
        // For this demo, we'll use the first tenant or the current context
        $tenant = \App\Models\Tenant::first();
        $settings = $tenant->settings['notifications'] ?? null;

        if (!$settings) {
            Log::warning("No notification settings found for tenant. Alert logged: {$message}");
            return;
        }

        $targets = [];
        foreach ($settings['channels'] as $type => $config) {
            if ($config['enabled'] && !empty($config['webhook_url'] ?? $config['chat_id'] ?? $config['phone_number'])) {
                $targets[$type] = $config['webhook_url'] ?? $config['chat_id'] ?? $config['phone_number'];
            }
        }

        if (!empty($targets)) {
            $this->notify($message, $targets);
            Log::info("Broadcasted alert '{$alertType}' to " . count($targets) . " channels.");
        }
    }
}
