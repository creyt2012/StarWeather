<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class EnsureHighAvailability
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            // Check if primary DB is alive
            DB::connection()->getPdo();
        } catch (\Exception $e) {
            // If DB is down and it's a mutation request, fail fast
            if (!$request->isMethod('GET')) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'System in Maintenance Mode (Primary DB Unreachable)',
                    'code' => 'FAILOVER_ACTIVE'
                ], 503);
            }

            // For GET requests, we could attempt to switch to a read replica
            // config(['database.connections.pgsql.read.host' => [env('DB_REPLICA_HOST')]]);
            // DB::purge('pgsql');
        }

        return $next($request);
    }
}
