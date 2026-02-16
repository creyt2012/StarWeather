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
            $table->foreignId('station_id')->nullable()->after('id')->constrained('ground_stations')->onDelete('set null');
            $table->float('temperature')->nullable()->after('risk_level');
            $table->float('humidity')->nullable()->after('temperature');
            $table->float('pressure')->nullable()->after('humidity');
            $table->string('wind_speed')->nullable()->after('pressure');
            $table->string('wind_direction')->nullable()->after('wind_speed');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('weather_metrics', function (Blueprint $table) {
            $table->dropForeign(['station_id']);
            $table->dropColumn(['station_id', 'temperature', 'humidity', 'pressure', 'wind_speed', 'wind_direction']);
        });
    }
};
