<?php

namespace Database\Factories;

use App\Modules\Commerce\Models\Package;
use App\Modules\Commerce\Models\Product;
use App\Shared\Enums\ProductKind;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Package>
 */
class PackageFactory extends Factory
{
    protected $model = Package::class;

    public function definition(): array
    {
        return [
            'product_id' => Product::factory()->state([
                'kind' => ProductKind::Package,
                'price_amount' => fake()->numberBetween(250, 850),
            ]),
            'billing_cycle_label' => fake()->randomElement(['شهري', '3 شهور', 'معسكر مكثف']),
            'lecture_count' => fake()->numberBetween(6, 24),
            'is_featured' => true,
        ];
    }
}
