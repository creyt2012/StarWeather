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
        Schema::create('alert_rules', function (Illuminate\Database\Schema\Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('parameter'); // temperature, humidity, rain_intensity, etc.
            $table->string('operator'); // >, <, =, trend_up, trend_down
            $table->float('threshold')->nullable();
            $table->string('severity')->default('INFO'); // INFO, WARNING, CRITICAL
            $table->boolean('is_active')->default(true);
            $table->json('channels')->nullable(); // channels to notify: telegram, zalo, etc.
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alert_rules');
    }
};
