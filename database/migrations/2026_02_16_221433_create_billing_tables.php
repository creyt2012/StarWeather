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
        // 1. Subscription Plans
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // FREE, PRO, ENTERPRISE
            $table->string('slug')->unique();
            $table->string('stripe_id')->nullable()->index();
            $table->string('paypal_id')->nullable()->index();
            $table->decimal('price', 10, 2)->default(0);
            $table->string('currency')->default('USD');
            $table->string('interval')->default('month'); // month, year
            $table->json('features')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Tenant Subscriptions
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('plan_id')->constrained()->onDelete('cascade');
            $table->string('status'); // active, trialing, cancelled, past_due
            $table->string('gateway'); // stripe, paypal
            $table->string('gateway_id')->nullable()->index();
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();
        });

        // 3. Update Tenants
        Schema::table('tenants', function (Blueprint $table) {
            $table->foreignId('plan_id')->nullable()->after('domain')->constrained()->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropForeign(['plan_id']);
            $table->dropColumn('plan_id');
        });
        Schema::dropIfExists('subscriptions');
        Schema::dropIfExists('plans');
    }
};
