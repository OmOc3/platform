<?php

namespace Database\Factories;

use App\Modules\Commerce\Models\Order;
use App\Modules\Students\Models\Student;
use App\Shared\Enums\OrderKind;
use App\Shared\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'student_id' => Student::factory(),
            'kind' => fake()->randomElement(OrderKind::cases()),
            'status' => OrderStatus::Paid,
            'subtotal_amount' => 0,
            'total_amount' => 0,
            'currency' => 'EGP',
            'placed_at' => now()->subDays(fake()->numberBetween(1, 40)),
            'meta' => null,
        ];
    }
}
