<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApiKey;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;

class ApiKeyManagementController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/ApiKeyManagement', [
            'apiKeys' => ApiKey::with('user')->get(),
            'tenants' => Tenant::all(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'tenant_id' => 'required|exists:tenants,id',
            'rate_limit' => 'required|integer|min:1',
            'monthly_quota' => 'required|integer|min:1',
        ]);

        ApiKey::create([
            ...$validated,
            'key' => 'sk_' . Str::random(32),
            'secret' => Str::random(64),
            'is_active' => true,
            'usage_count' => 0,
        ]);

        return redirect()->back()->with('success', 'API Key generated successfully');
    }

    public function update(Request $request, ApiKey $apiKey)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'is_active' => 'required|boolean',
            'rate_limit' => 'required|integer|min:1',
            'monthly_quota' => 'required|integer|min:1',
        ]);

        $apiKey->update($validated);

        return redirect()->back()->with('success', 'API Key updated');
    }

    public function destroy(ApiKey $apiKey)
    {
        $apiKey->delete();
        return redirect()->back()->with('success', 'API Key revoked');
    }
}
