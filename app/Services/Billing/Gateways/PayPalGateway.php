<?php

namespace App\Services\Billing\Gateways;

use App\Models\Plan;
use App\Models\Tenant;
use Illuminate\Support\Facades\Log;

class PayPalGateway implements PaymentGatewayInterface
{
    public function createSubscription(Tenant $tenant, Plan $plan): string
    {
        Log::info("Creating PayPal Subscription for Tenant: {$tenant->id}, Plan: {$plan->slug}");
        return "paypal_subs_pending_" . uniqid();
    }

    public function cancelSubscription(string $subscriptionId): bool
    {
        Log::info("Cancelling PayPal Subscription: {$subscriptionId}");
        return true;
    }

    public function handleWebhook(array $payload): bool
    {
        Log::info("Handling PayPal Webhook");
        return true;
    }
}
