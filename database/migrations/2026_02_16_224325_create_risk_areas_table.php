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
        Schema::create('risk_areas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('name')->nullable();
            $table->string('type'); // FLOOD, WILDFIRE, CYCLONE, etc.
            $table->string('severity')->default('LOW'); // LOW, MEDIUM, HIGH, CRITICAL
            $table->json('geometry'); // GeoJSON feature geometry
            $table->json('metadata')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('risk_areas');
    }
};
