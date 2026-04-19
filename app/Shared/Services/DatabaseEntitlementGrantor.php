<?php

namespace App\Shared\Services;

use App\Shared\Contracts\EntitlementGrantor;
use BadMethodCallException;

class DatabaseEntitlementGrantor implements EntitlementGrantor
{
    public function grant(array $payload): mixed
    {
        throw new BadMethodCallException('Entitlement granting is not implemented in Milestone 1.');
    }
}
