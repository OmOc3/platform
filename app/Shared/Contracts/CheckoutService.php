<?php

namespace App\Shared\Contracts;

interface CheckoutService
{
    public function beginDigitalCheckout(array $payload): mixed;

    public function beginBookCheckout(array $payload): mixed;
}
