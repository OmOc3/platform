<?php

namespace App\Shared\Services;

use App\Shared\Contracts\CheckoutService;
use BadMethodCallException;

class DatabaseCheckoutService implements CheckoutService
{
    public function beginDigitalCheckout(array $payload): mixed
    {
        throw new BadMethodCallException('Digital checkout is not implemented in Milestone 1.');
    }

    public function beginBookCheckout(array $payload): mixed
    {
        throw new BadMethodCallException('Book checkout is not implemented in Milestone 1.');
    }
}
