<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AlertRule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AlertRuleController extends Controller
{
    /**
     * List all alert rules.
     */
    public function index(): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => AlertRule::latest()->get()
        ]);
    }

    /**
     * Create a new alert rule.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'parameter' => 'required|string',
            'operator' => 'required|string',
            'threshold' => 'nullable|numeric',
            'severity' => 'required|string',
            'is_active' => 'boolean',
            'channels' => 'nullable|array'
        ]);

        $rule = AlertRule::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Alert rule initialized',
            'data' => $rule
        ], 201);
    }

    /**
     * Show a specific rule.
     */
    public function show(AlertRule $rule): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => $rule
        ]);
    }

    /**
     * Update an alert rule.
     */
    public function update(Request $request, AlertRule $rule): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'parameter' => 'sometimes|string',
            'operator' => 'sometimes|string',
            'threshold' => 'sometimes|nullable|numeric',
            'severity' => 'sometimes|string',
            'is_active' => 'sometimes|boolean',
            'channels' => 'sometimes|nullable|array'
        ]);

        $rule->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Alert rule patched',
            'data' => $rule
        ]);
    }

    /**
     * Delete an alert rule.
     */
    public function destroy(AlertRule $rule): JsonResponse
    {
        $rule->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Alert rule purged'
        ]);
    }
}
