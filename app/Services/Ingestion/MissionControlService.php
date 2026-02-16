<?php

namespace App\Services\Ingestion;

use App\Models\MissionFile;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class MissionControlService
{
    /**
     * Upload and register a new mission file.
     */
    public function upload(UploadedFile $file, string $type, int $tenantId): MissionFile
    {
        $path = $file->store('mission_files/' . $tenantId);

        return MissionFile::create([
            'tenant_id' => $tenantId,
            'original_name' => $file->getClientOriginalName(),
            'stored_path' => $path,
            'mime_type' => $file->getClientMimeType(),
            'type' => $type,
            'status' => 'PENDING',
        ]);
    }

    /**
     * Dispatch the appropriate ingestion job based on file type.
     */
    public function process(MissionFile $file): void
    {
        $file->update(['status' => 'PROCESSING']);

        try {
            switch ($file->type) {
                case 'EXCEL_WEATHER':
                    \App\Jobs\ExcelIngestionJob::dispatch($file);
                    Log::info("Dispatched ExcelIngestionJob for file ID: {$file->id}");
                    break;
                case 'GEOJSON_RISK':
                    \App\Jobs\GeoJsonIngestionJob::dispatch($file);
                    Log::info("Dispatched GeoJsonIngestionJob for file ID: {$file->id}");
                    break;
                default:
                    throw new \Exception("Unsupported mission file type: {$file->type}");
            }
        } catch (\Exception $e) {
            $file->update([
                'status' => 'FAILED',
                'error_message' => $e->getMessage()
            ]);
            Log::error("Mission control process failed: " . $e->getMessage());
        }
    }
}
