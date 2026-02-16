<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GroundStation;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class GroundStationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        return Inertia::render('Admin/GroundStations/Index', [
            'stations' => GroundStation::with('latestMetric')->latest()->get()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:ground_stations,code',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'type' => 'required|string',
            'status' => 'required|string'
        ]);

        GroundStation::create($validated);

        return redirect()->back()->with('success', 'Ground station created successfully.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, GroundStation $station)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'type' => 'required|string',
            'status' => 'required|string'
        ]);

        $station->update($validated);

        return redirect()->back()->with('success', 'Ground station updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(GroundStation $station)
    {
        $station->delete();

        return redirect()->back()->with('success', 'Ground station deleted successfully.');
    }
}
