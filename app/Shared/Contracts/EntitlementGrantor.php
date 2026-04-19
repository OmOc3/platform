<?php

namespace App\Shared\Contracts;

interface EntitlementGrantor
{
    public function grant(array $payload): mixed;
}
