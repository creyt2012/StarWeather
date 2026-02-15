<?php

namespace App\Events;

use App\Models\Satellite;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SatelliteUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public array $data;

    public function __construct(Satellite $satellite, array $trackData)
    {
        $this->data = array_merge([
            'id' => $satellite->id,
            'name' => $satellite->name,
            'norad_id' => $satellite->norad_id,
        ], $trackData);
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('satellites.live'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'satellite.updated';
    }
}
