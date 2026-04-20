<?php

namespace App\Shared\Contracts;

interface PaymentProviderRegistry
{
    public function driver(?string $name = null): PaymentProvider;
}
