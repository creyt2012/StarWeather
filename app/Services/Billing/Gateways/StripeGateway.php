<?php

namespace App\Services\Billing\Gateways;

use App\Models\Plan;
use App\Models\Tenant;
use Illuminate\Support\Facades\Log;

class StripeGateway implements PaymentGatewayInterface
{
    public function createSubscription(Tenant $tenant, Plan $plan): string
    {
        // Implementation logic for Stripe Checkout or Subscription API
        Log::info("Creating Stripe Subscription for Tenant: {$tenant->id}, Plan: {$plan->slug}");
        return "stripe_subs_pending_" . uniqid();
    }

    public function cancelSubscription(string $subscriptionId): bool
    {
        Log::info("Cancelling Stripe Subscription: {$subscriptionId}");
        return true;
    }

    public function handleWebhook(array $payload): bool
    {
        Log::info("Handling Stripe Webhook");
        return true;
    }
}
