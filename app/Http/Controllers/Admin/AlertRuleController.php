<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AlertRule;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AlertRuleController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Alerts/Rules', [
            'rules' => AlertRule::latest()->get()
        ]);
    }

    public function store(Request $request)
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

        AlertRule::create($validated);

        return redirect()->back()->with('success', 'Alert rule created successfully.');
    }

    public function update(Request $request, AlertRule $rule)
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

        $rule->update($validated);

        return redirect()->back()->with('success', 'Alert rule updated successfully.');
    }

    public function destroy(AlertRule $rule)
    {
        $rule->delete();
        return redirect()->back()->with('success', 'Alert rule deleted successfully.');
    }
}
