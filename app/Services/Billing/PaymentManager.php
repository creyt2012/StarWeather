<?php

namespace App\Services\Billing;

use App\Services\Billing\Gateways\StripeGateway;
use App\Services\Billing\Gateways\PayPalGateway;
use Illuminate\Support\Manager;

class PaymentManager extends Manager
{
    public function getDefaultDriver()
    {
        return config('billing.default', 'stripe');
    }

    public function createStripeDriver()
    {
        return new StripeGateway();
    }

    public function createPaypalDriver()
    {
        return new PayPalGateway();
    }
}
