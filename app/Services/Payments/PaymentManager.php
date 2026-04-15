<?php

namespace App\Services\Payments;

use App\Payments\Contracts\PaymentGateway;
use InvalidArgumentException;

class PaymentManager
{
    public function gatewayForMethod(string $paymentMethod): PaymentGateway
    {
        $gatewayClass = config("payments.methods.{$paymentMethod}");

        if (! is_string($gatewayClass) || $gatewayClass === '') {
            throw new InvalidArgumentException("Unsupported payment method [{$paymentMethod}].");
        }

        $gateway = app($gatewayClass);

        if (! $gateway instanceof PaymentGateway) {
            throw new InvalidArgumentException("Payment gateway [{$gatewayClass}] must implement the payment gateway contract.");
        }

        return $gateway;
    }

    public function gatewayForProvider(string $provider): PaymentGateway
    {
        return $this->gatewayForMethod($provider);
    }
}
