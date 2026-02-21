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
        Schema::create('radar_stations', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('code')->unique()->comment('3-letter or numeric code');
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->integer('elevation_m')->default(0);
            $table->string('frequency_band')->nullable()->comment('S-band, C-band, X-band');
            $table->integer('coverage_radius_km')->default(250);
            $table->enum('status', ['operational', 'maintenance', 'offline'])->default('operational');
            $table->json('parameters')->nullable()->comment('Dynamic config for this radar');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('radar_stations');
    }
};
