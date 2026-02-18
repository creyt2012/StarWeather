<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Conjunction;
use Illuminate\Http\JsonResponse;

class ConjunctionController extends Controller
{
    /**
     * Get all active satellite conjunctions/close approaches.
     */
    public function index(): JsonResponse
    {
        $conjunctions = Conjunction::with(['satelliteA', 'satelliteB'])
            ->where('status', 'ACTIVE')
            ->orderBy('tca', 'asc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $conjunctions,
            'source' => 'Orbital Safety Network (OSN)',
            'timestamp' => now()->toIso8601String()
        ]);
    }

    /**
     * Show detailed conjunction info.
     */
    public function show(Conjunction $conjunction): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => $conjunction->load(['satelliteA', 'satelliteB'])
        ]);
    }
}
