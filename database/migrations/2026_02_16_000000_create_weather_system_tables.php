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
            $blueprint->string('plan')->default('FREE');
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

        // 4. Satellite Tracks (Missing previously)
        Schema::create('satellite_tracks', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->foreignId('satellite_id')->constrained()->onDelete('cascade');
            $blueprint->decimal('latitude', 10, 8);
            $blueprint->decimal('longitude', 11, 8);
            $blueprint->float('altitude')->nullable();
            $blueprint->float('velocity')->nullable();
            $blueprint->timestamp('tracked_at')->index();
            $blueprint->timestamps();
        });

        // 5. Unified Weather Metrics
        Schema::create('weather_metrics', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->decimal('latitude', 10, 8)->nullable();
            $blueprint->decimal('longitude', 11, 8)->nullable();
            $blueprint->float('cloud_coverage')->default(0);
            $blueprint->float('rain_intensity')->default(0);
            $blueprint->float('risk_score')->default(0);
            $blueprint->string('risk_level')->default('LOW');
            $blueprint->string('source')->nullable();
            $blueprint->timestamp('captured_at')->index();
            $blueprint->json('data_sources')->nullable();
            $blueprint->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weather_metrics');
        Schema::dropIfExists('satellite_tracks');
        Schema::dropIfExists('satellites');
        Schema::dropIfExists('api_keys');
        Schema::dropIfExists('tenants');
    }
};
