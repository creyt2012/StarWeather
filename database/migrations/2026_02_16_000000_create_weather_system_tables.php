<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // 1. Tenants (Commercial SaaS)
        Schema::create('tenants', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->string('name');
            $blueprint->string('domain')->unique()->nullable();
            $blueprint->string('plan')->default('FREE'); // FREE, PRO, ENTERPRISE, GOV
            $blueprint->json('settings')->nullable();
            $blueprint->timestamp('expires_at')->nullable();
            $blueprint->timestamps();
        });

        // 2. API Keys
        Schema::create('api_keys', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $blueprint->string('key')->unique();
            $blueprint->string('secret')->nullable();
            $blueprint->string('name')->nullable();
            $blueprint->boolean('is_active')->default(true);
            $blueprint->timestamp('last_used_at')->nullable();
            $blueprint->timestamps();
        });

        // 3. Satellites
        Schema::create('satellites', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->string('norad_id')->unique();
            $blueprint->string('name');
            $blueprint->text('tle_line1')->nullable();
            $blueprint->text('tle_line2')->nullable();
            $blueprint->string('type')->default('WEATHER');
            $blueprint->string('status')->default('ACTIVE');
            $blueprint->timestamps();
        });

        // 4. Ground Stations
        Schema::create('ground_stations', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->string('station_id')->unique();
            $blueprint->string('name');
            $blueprint->decimal('latitude', 10, 8);
            $blueprint->decimal('longitude', 11, 8);
            $blueprint->string('country_code', 2)->nullable();
            $blueprint->timestamps();
        });

        // 5. Unified Weather Metrics (The Data Fusion table)
        // Note: For large systems, this should be partitioned.
        Schema::create('weather_metrics', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->decimal('latitude', 10, 8)->index();
            $blueprint->decimal('longitude', 11, 8)->index();

            // Core Metrics
            $blueprint->float('cloud_coverage')->nullable(); // %
            $blueprint->float('cloud_density')->nullable();
            $blueprint->float('rain_intensity')->nullable(); // mm/h
            $blueprint->float('temperature')->nullable();
            $blueprint->float('humidity')->nullable();
            $blueprint->float('pressure')->nullable();

            // Computed Scores
            $blueprint->float('risk_score')->default(0);
            $blueprint->string('risk_level')->default('LOW');
            $blueprint->float('confidence')->default(1.0);

            // Metadata
            $blueprint->timestamp('timestamp')->index();
            $blueprint->json('data_sources')->nullable(); // Himawari, Radar, etc.
            $blueprint->timestamps();
        });

        // 6. Alerts
        Schema::create('alerts', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->string('type'); // RAIN, CLOUD_GROWTH, RISK
            $blueprint->string('severity')->default('INFO'); // INFO, WARNING, CRITICAL
            $blueprint->decimal('latitude', 10, 8)->nullable();
            $blueprint->decimal('longitude', 11, 8)->nullable();
            $blueprint->text('message');
            $blueprint->json('payload')->nullable();
            $blueprint->timestamp('detected_at');
            $blueprint->timestamp('resolved_at')->nullable();
            $blueprint->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alerts');
        Schema::dropIfExists('weather_metrics');
        Schema::dropIfExists('ground_stations');
        Schema::dropIfExists('satellites');
        Schema::dropIfExists('api_keys');
        Schema::dropIfExists('tenants');
    }
};
