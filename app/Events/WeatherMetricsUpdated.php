<?php

namespace App\Events;

use App\Models\WeatherMetric;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WeatherMetricsUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public array $data;

    public function __construct(WeatherMetric $metric, array $riskData)
    {
        $this->data = [
            'timestamp' => $metric->captured_at,
            'cloud_coverage' => $metric->cloud_coverage,
            'cloud_density' => $metric->cloud_density,
            'rain_intensity' => $metric->rain_intensity,
            'pressure' => $metric->pressure,
            'risk_score' => $metric->risk_score,
            'risk_level' => $metric->risk_level,
            'confidence_score' => $metric->confidence_score,
            'provenance' => $metric->provenance,
            'image_url' => $metric->metadata['image_url'] ?? null,
        ];
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('weather.live'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'weather.updated';
    }
}
