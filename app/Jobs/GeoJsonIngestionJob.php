<?php

namespace App\Jobs;

use App\Models\MissionFile;
use App\Models\RiskArea;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class GeoJsonIngestionJob implements ShouldQueue
{
    use Queueable;

    protected $missionFile;

    public function __construct(MissionFile $missionFile)
    {
        $this->missionFile = $missionFile;
    }

    /**
     * Execute the job to parse GeoJSON and store risk areas.
     */
    public function handle(): void
    {
        $this->missionFile->update(['status' => 'PROCESSING']);

        try {
            $content = Storage::get($this->missionFile->stored_path);
            $geoJson = json_decode($content, true);

            if (!$geoJson || !isset($geoJson['features'])) {
                throw new \Exception("Invalid GeoJSON format. Missing 'features' array.");
            }

            $count = 0;
            foreach ($geoJson['features'] as $feature) {
                $props = $feature['properties'] ?? [];

                RiskArea::create([
                    'tenant_id' => $this->missionFile->tenant_id,
                    'name' => $props['name'] ?? 'Imported Area ' . ($count + 1),
                    'type' => $props['type'] ?? 'GENERAL_RISK',
                    'severity' => $props['severity'] ?? 'MEDIUM',
                    'geometry' => $feature['geometry'],
                    'metadata' => [
                        'source_file' => $this->missionFile->original_name,
                        'imported_properties' => $props
                    ]
                ]);
                $count++;
            }

            $this->missionFile->update([
                'status' => 'COMPLETED',
                'processed_at' => now(),
                'metadata' => [
                    'feature_count' => $count,
                    'message' => "Successfully imported {$count} risk areas from GeoJSON."
                ]
            ]);

            Log::info("Mission Control: GeoJSON ingestion completed for file ID: {$this->missionFile->id}");
        } catch (\Exception $e) {
            $this->missionFile->update([
                'status' => 'FAILED',
                'error_message' => $e->getMessage()
            ]);
            Log::error("Mission Control: GeoJSON ingestion failed: " . $e->getMessage());
        }
    }
}
