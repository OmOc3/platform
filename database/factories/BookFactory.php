<?php

namespace Database\Factories;

use App\Modules\Commerce\Enums\BookAvailability;
use App\Modules\Commerce\Models\Book;
use App\Modules\Commerce\Models\Product;
use App\Shared\Enums\ProductKind;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Book>
 */
class BookFactory extends Factory
{
    protected $model = Book::class;

    public function definition(): array
    {
        return [
            'product_id' => Product::factory()->state([
                'kind' => ProductKind::Book,
                'price_amount' => fake()->numberBetween(120, 280),
            ]),
            'author_name' => 'أ. فيزياء',
            'page_count' => fake()->numberBetween(80, 240),
            'stock_quantity' => fake()->numberBetween(5, 40),
            'cover_badge' => fake()->randomElement(['الأكثر طلبًا', 'طبعة 2026', 'جديد']),
            'availability_status' => BookAvailability::InStock,
            'metadata' => null,
        ];
    }
}
