<?php

namespace Database\Factories;

use App\Modules\Commerce\Models\Product;
use App\Shared\Enums\ProductKind;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $name = fake()->randomElement([
            'باقة الإتقان الشهرية',
            'مراجعة الفيزياء الشاملة',
            'كتاب الأفكار الذكية',
        ]);

        return [
            'uuid' => (string) Str::uuid(),
            'kind' => fake()->randomElement(ProductKind::cases()),
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(10, 99),
            'name_ar' => $name,
            'name_en' => null,
            'teaser' => fake('ar_EG')->sentence(),
            'description' => fake('ar_EG')->paragraph(),
            'price_amount' => fake()->numberBetween(0, 1500),
            'currency' => 'EGP',
            'thumbnail_url' => null,
            'is_active' => true,
            'is_featured' => true,
            'published_at' => now()->subDays(fake()->numberBetween(1, 20)),
        ];
    }
}
