<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Satellite;
use App\Models\SatelliteTrack;
use Illuminate\Http\JsonResponse;

class SatelliteController extends Controller
{
    protected \App\Repositories\StateRepository $stateRepo;

    public function __construct(\App\Repositories\StateRepository $stateRepo)
    {
        $this->stateRepo = $stateRepo;
    }

    /**
     * Get live positions of all active satellites using L1 Cache.
     */
    public function index(): JsonResponse
    {
        $states = $this->stateRepo->getSatelliteStates();

        if (empty($states)) {
            // Fallback to DB if cache is cold
            return $this->indexFromDb();
        }

        return response()->json([
            'status' => 'success',
            'data' => array_values($states)
        ]);
    }

    private function indexFromDb(): JsonResponse
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
