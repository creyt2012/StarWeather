<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class HealthController extends Controller
{
    /**
     * Check system health (DB, Redis, Queue).
     */
    public function check(): JsonResponse
    {
        $status = [
            'status' => 'operational',
            'timestamp' => now()->toIso8601String(),
            'services' => [
                'database' => $this->checkDatabase(),
                'cache' => $this->checkCache(),
                'vitals' => [
                    'php_version' => PHP_VERSION,
                    'environment' => app()->environment(),
                ]
            ]
        ];

        $statusCode = $this->isOperational($status) ? 200 : 503;

        return response()->json($status, $statusCode);
    }

    private function checkDatabase(): array
    {
        try {
            DB::connection()->getPdo();
            return ['status' => 'connected', 'latency' => $this->measure(fn() => DB::select('SELECT 1'))];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    private function checkCache(): array
    {
        try {
            Cache::put('health_check', true, 10);
            return ['status' => 'operational', 'alive' => Cache::has('health_check')];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    private function measure(callable $callback): string
    {
        $start = microtime(true);
        $callback();
        return round((microtime(true) - $start) * 1000, 2) . 'ms';
    }

    private function isOperational(array $status): bool
    {
        return $status['services']['database']['status'] === 'connected'
            && $status['services']['cache']['status'] === 'operational';
    }
}
