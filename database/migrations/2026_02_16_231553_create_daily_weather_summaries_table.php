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
        Schema::create('daily_weather_summaries', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->string('parameter'); // temperature, humidity, wind_speed, etc.
            $table->float('avg_value');
            $table->float('min_value');
            $table->float('max_value');
            $table->integer('sample_count');
            $table->timestamps();

            $table->index(['latitude', 'longitude', 'date']);
            $table->unique(['date', 'latitude', 'longitude', 'parameter'], 'daily_weather_summary_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_weather_summaries');
    }
};
