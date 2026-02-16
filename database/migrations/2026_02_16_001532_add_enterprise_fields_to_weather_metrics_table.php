<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('weather_metrics', function (Blueprint $table) {
            $table->float('cloud_density')->default(0)->after('cloud_coverage');
            $table->float('pressure')->default(1013.25)->after('rain_intensity');
            $table->float('cloud_growth_rate')->default(0)->after('pressure');
            $table->float('confidence_score')->default(100)->after('risk_level');
            $table->json('provenance')->nullable()->after('data_sources');
        });
    }

    public function down(): void
    {
        Schema::table('weather_metrics', function (Blueprint $table) {
            $table->dropColumn(['cloud_density', 'pressure', 'cloud_growth_rate', 'confidence_score', 'provenance']);
        });
    }
};
