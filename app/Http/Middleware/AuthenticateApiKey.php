<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\ApiKey;
use App\Services\TenantManager;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateApiKey
{
    protected $tenantManager;

    public function __construct(TenantManager $tenantManager)
    {
        $this->tenantManager = $tenantManager;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $keyString = $request->header('X-API-KEY');

        if ($keyString) {
            $apiKey = ApiKey::where('key', $keyString)->active()->with('tenant')->first();

            if (!$apiKey) {
                return response()->json(['error' => 'Invalid or inactive API Key.'], 401);
            }

            // Set the global tenant context
            $this->tenantManager->setTenant($apiKey->tenant);

            // Update last used timestamp (proactive update)
            $apiKey->update(['last_used_at' => now()]);

            return $next($request);
        }

        // --- Hybrid Fallback: Check if user is authenticated via standard session ---
        if (auth('web')->check()) {
            return $next($request);
        }

        return response()->json(['error' => 'Unauthenticated. API Key missing or Session expired.'], 401);
    }
}
