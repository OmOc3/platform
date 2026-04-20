<?php

namespace App\Shared\Services;

use App\Shared\Contracts\PaymentProvider;
use App\Shared\Contracts\PaymentProviderRegistry;
use InvalidArgumentException;

class ConfiguredPaymentProviderRegistry implements PaymentProviderRegistry
{
    /**
     * @var array<string, class-string<PaymentProvider>>
     */
    private array $drivers = [
        'fake' => FakePaymentProvider::class,
    ];

    public function driver(?string $name = null): PaymentProvider
    {
        $name ??= (string) config('services.commerce.default_payment_provider', 'fake');
        $driver = $this->drivers[$name] ?? null;

        if ($driver === null) {
            throw new InvalidArgumentException("Unsupported payment provider [{$name}].");
        }

        return app($driver);
    }
}
