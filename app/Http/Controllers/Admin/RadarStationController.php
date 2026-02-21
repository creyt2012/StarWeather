<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RadarStation;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RadarStationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        return Inertia::render('Admin/RadarStations/Index', [
            'stations' => RadarStation::latest()->get()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:radar_stations,code',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'elevation_m' => 'required|numeric',
            'frequency_band' => 'required|string',
            'coverage_radius_km' => 'required|numeric',
            'status' => 'required|string',
            'parameters' => 'nullable|array'
        ]);

        RadarStation::create($validated);

        return redirect()->back()->with('success', 'Radar station configured successfully.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, RadarStation $station)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'elevation_m' => 'required|numeric',
            'frequency_band' => 'required|string',
            'coverage_radius_km' => 'required|numeric',
            'status' => 'required|string',
            'parameters' => 'nullable|array'
        ]);

        $station->update($validated);

        return redirect()->back()->with('success', 'Radar station updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(RadarStation $station)
    {
        $station->delete();

        return redirect()->back()->with('success', 'Radar station removed successfully.');
    }
}
