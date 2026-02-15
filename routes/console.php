<?php

use Illuminate\Support\Facades\Schedule;
use App\Jobs\HimawariIngestJob;
use App\Jobs\SatellitePropagateJob;

Schedule::job(new HimawariIngestJob)->everyTenMinutes();
Schedule::job(new SatellitePropagateJob)->everyFiveSeconds();
