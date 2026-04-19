<?php

namespace Database\Factories;

use App\Modules\Commerce\Models\Entitlement;
use App\Modules\Commerce\Models\OrderItem;
use App\Modules\Commerce\Models\Product;
use App\Modules\Students\Models\Student;
use App\Shared\Enums\EntitlementSource;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Entitlement>
 */
class EntitlementFactory extends Factory
{
    protected $model = Entitlement::class;

    public function definition(): array
    {
        $price = fake()->numberBetween(0, 900);

        return [
            'student_id' => Student::factory(),
            'product_id' => Product::factory(),
            'order_item_id' => null,
            'source' => fake()->randomElement(EntitlementSource::cases()),
            'status' => 'active',
            'item_name_snapshot' => fake()->randomElement(['باقة فيزياء', 'مراجعة نهائية', 'منتج رقمي']),
            'price_amount' => $price,
            'currency' => 'EGP',
            'granted_by_admin_id' => null,
            'granted_at' => now()->subDays(fake()->numberBetween(1, 30)),
            'starts_at' => now()->subDays(fake()->numberBetween(1, 30)),
            'ends_at' => now()->addDays(fake()->numberBetween(15, 120)),
            'meta' => null,
        ];
    }
}
