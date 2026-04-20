<?php

namespace Database\Factories;

use App\Modules\Commerce\Models\Order;
use App\Modules\Commerce\Models\Payment;
use App\Modules\Commerce\Models\PaymentWebhookReceipt;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PaymentWebhookReceipt>
 */
class PaymentWebhookReceiptFactory extends Factory
{
    protected $model = PaymentWebhookReceipt::class;

    public function definition(): array
    {
        return [
            'provider' => 'fake',
            'event_key' => 'evt_'.fake()->unique()->numerify('########'),
            'payment_id' => Payment::factory(),
            'order_id' => Order::factory(),
            'status' => 'paid',
            'payload' => ['status' => 'paid'],
            'processed_at' => now(),
            'meta' => null,
        ];
    }
}
