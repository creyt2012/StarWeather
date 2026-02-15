<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Satellite;
use App\Models\SatelliteTrack;
use Illuminate\Http\JsonResponse;

class SatelliteController extends Controller
{
    /**
     * Get live positions of all active satellites.
     */
    public function index(): JsonResponse
    {
        $satellites = Satellite::where('status', 'ACTIVE')
            ->get()
            ->map(function ($sat) {
                $latestTrack = SatelliteTrack::where('satellite_id', $sat->id)
                    ->latest('tracked_at')
                    ->first();

                return [
                    'id' => $sat->id,
                    'name' => $sat->name,
                    'norad_id' => $sat->norad_id,
                    'last_track' => $latestTrack ? [
                        'latitude' => $latestTrack->latitude,
                        'longitude' => $latestTrack->longitude,
                        'altitude' => $latestTrack->altitude,
                        'velocity' => $latestTrack->velocity,
                        'timestamp' => $latestTrack->tracked_at,
                    ] : null
                ];
            });

        return response()->json([
            'status' => 'success',
            'data' => $satellites
        ]);
    }
}
