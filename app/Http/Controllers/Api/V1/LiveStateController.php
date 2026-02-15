<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\WeatherMetric;
use App\Services\TenantManager;
use Illuminate\Http\Request;

class LiveStateController extends Controller
{
    protected $tenantManager;

    public function __construct(TenantManager $tenantManager)
    {
        $this->tenantManager = $tenantManager;
    }

    /**
     * Get the latest unified weather state.
     */
    public function index(Request $request)
    {
        $tenant = $this->tenantManager->getTenant();

        // Fetch latest metrics
        $latest = WeatherMetric::orderBy('timestamp', 'desc')->first();

        return response()->json([
            'tenant' => $tenant->name,
            'plan' => $tenant->plan,
            'timestamp' => $latest?->timestamp?->toIso8601String(),
            'data' => $latest,
            'source' => 'Japan Meteorological Agency (Himawari-9)',
        ]);
    }
}
