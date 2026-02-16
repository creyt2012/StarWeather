<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('weather_metrics', function (Blueprint $table) {
            if (!Schema::hasColumn('weather_metrics', 'station_id')) {
                $table->foreignId('station_id')->nullable()->after('id')->constrained('ground_stations')->onDelete('set null');
            }
            if (!Schema::hasColumn('weather_metrics', 'temperature')) {
                $table->float('temperature')->nullable()->after('risk_level');
            }
            if (!Schema::hasColumn('weather_metrics', 'humidity')) {
                $table->float('humidity')->nullable()->after('temperature');
            }
            // pressure is already added in a previous migration, skipping
            if (!Schema::hasColumn('weather_metrics', 'wind_speed')) {
                $table->string('wind_speed')->nullable()->after('pressure');
            }
            if (!Schema::hasColumn('weather_metrics', 'wind_direction')) {
                $table->string('wind_direction')->nullable()->after('wind_speed');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('weather_metrics', function (Blueprint $table) {
            $table->dropForeign(['station_id']);
            $table->dropColumn(['station_id', 'temperature', 'humidity', 'wind_speed', 'wind_direction']);
        });
    }
};
