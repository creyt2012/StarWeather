<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\HimawariIngestJob;
use App\Jobs\SatellitePropagateJob;

class TestWeatherSystem extends Command
{
    protected $signature = 'weather:test';
    protected $description = 'Trigger ingestion jobs for testing';

    public function handle()
    {
        $this->info('Dispatching HimawariIngestJob...');
        HimawariIngestJob::dispatch();

        $this->info('Dispatching SatellitePropagateJob...');
        SatellitePropagateJob::dispatch();

        $this->info('Jobs dispatched. Check logs or database for results.');
    }
}
