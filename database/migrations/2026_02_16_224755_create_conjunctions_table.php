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
        Schema::create('conjunctions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('satellite_a_id');
            $table->unsignedBigInteger('satellite_b_id');
            $table->float('distance'); // Minimum distance in km
            $table->float('probability')->nullable(); // Collision probability
            $table->timestamp('tca'); // Time of Closest Approach
            $table->string('status')->default('WARNING'); // WARNING, CRITICAL, RESOLVED
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('satellite_a_id')->references('id')->on('satellites')->onDelete('cascade');
            $table->foreign('satellite_b_id')->references('id')->on('satellites')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conjunctions');
    }
};
