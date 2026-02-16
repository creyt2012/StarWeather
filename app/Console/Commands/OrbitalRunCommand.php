<?php

namespace App\Console\Commands;

use App\Jobs\SatellitePropagateJob;
use App\Jobs\HimawariIngestJob;
use Illuminate\Console\Command;

class OrbitalRunCommand extends Command
{
    protected $signature = 'orbital:run {--interval=5 : Propagation interval in seconds}';
    protected $description = 'Run the real-time orbital simulation engine';

    public function handle()
    {
        $this->info("Orbital Engine Started. Press Ctrl+C to stop.");
        $interval = (int) $this->option('interval');
        $tick = 0;

        while (true) {
            $this->comment("[" . now()->toTimeString() . "] Ticking orbital mechanics...");

            // 1. Propagate Satellites (Every tick)
            dispatch_sync(new SatellitePropagateJob());

            // 2. Ingest Weather (Every 60 ticks = ~5 mins)
            if ($tick % 60 === 0) {
                $this->info("Ingesting new Himawari imagery...");
                dispatch_sync(new HimawariIngestJob());
            }

            $tick++;
            sleep($interval);
        }
    }
}
