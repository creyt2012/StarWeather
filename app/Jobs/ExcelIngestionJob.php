<?php

namespace App\Jobs;

use App\Models\MissionFile;
use App\Imports\WeatherMetricsImport;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;

class ExcelIngestionJob implements ShouldQueue
{
    use Queueable;

    protected $missionFile;

    public function __construct(MissionFile $missionFile)
    {
        $this->missionFile = $missionFile;
    }

    /**
     * Execute the job to parse Excel and import weather metrics.
     */
    public function handle(): void
    {
        $this->missionFile->update(['status' => 'PROCESSING']);

        try {
            Excel::import(new WeatherMetricsImport, $this->missionFile->stored_path);

            $this->missionFile->update([
                'status' => 'COMPLETED',
                'processed_at' => now(),
                'metadata' => [
                    'message' => 'Successfully imported batch data from Excel.'
                ]
            ]);

            Log::info("Mission Control: Excel ingestion completed for file ID: {$this->missionFile->id}");
        } catch (\Exception $e) {
            $this->missionFile->update([
                'status' => 'FAILED',
                'error_message' => $e->getMessage()
            ]);
            Log::error("Mission Control: Excel ingestion failed: " . $e->getMessage());
        }
    }
}
