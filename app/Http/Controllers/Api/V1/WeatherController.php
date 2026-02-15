<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\WeatherMetric;
use App\Engines\Analytics\RiskEngine;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WeatherController extends Controller
{
    protected RiskEngine $riskEngine;

    public function __construct(RiskEngine $riskEngine)
    {
        $this->riskEngine = $riskEngine;
    }

    /**
     * Get latest weather metrics and risk assessment.
     */
    public function latest(): JsonResponse
    {
        $metric = WeatherMetric::latest('captured_at')->first();

        if (!$metric) {
            return response()->json(['status' => 'error', 'message' => 'No weather data available'], 404);
        }

        $previous = WeatherMetric::where('captured_at', '<', $metric->captured_at)
            ->latest('captured_at')
            ->first();

        $risk = $this->riskEngine->computeRiskScore($metric, $previous);

        return response()->json([
            'status' => 'success',
            'data' => [
                'cloud_coverage' => $metric->cloud_coverage,
                'rain_intensity' => $metric->rain_intensity,
                'captured_at' => $metric->captured_at,
                'risk' => $risk
            ]
        ]);
    }

    /**
     * Get historical metrics for trends.
     */
    public function metrics(Request $request): JsonResponse
    {
        $hours = $request->get('hours', 24);
        $metrics = WeatherMetric::where('captured_at', '>=', now()->subHours($hours))
            ->orderBy('captured_at', 'asc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $metrics
        ]);
    }
}
