<?php

namespace Database\Factories;

use App\Modules\Commerce\Models\Order;
use App\Modules\Commerce\Models\Payment;
use App\Shared\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'attempt_number' => 1,
            'provider' => 'fake',
            'status' => PaymentStatus::Pending,
            'amount' => 100,
            'currency' => 'EGP',
            'provider_reference' => 'fake_ref_'.fake()->unique()->numerify('########'),
            'provider_transaction_reference' => null,
            'checkout_url' => 'https://example.test/fake-checkout',
            'expires_at' => now()->addMinutes(30),
            'paid_at' => null,
            'failed_at' => null,
            'canceled_at' => null,
            'refunded_at' => null,
            'failure_code' => null,
            'failure_message' => null,
            'meta' => null,
        ];
    }
}
