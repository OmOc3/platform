<?php

namespace Database\Factories;

use App\Modules\Commerce\Models\Order;
use App\Modules\Commerce\Models\OrderItem;
use App\Modules\Commerce\Models\Product;
use App\Shared\Enums\ProductKind;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderItem>
 */
class OrderItemFactory extends Factory
{
    protected $model = OrderItem::class;

    public function definition(): array
    {
        $kind = fake()->randomElement(ProductKind::cases());
        $price = fake()->numberBetween(0, 900);

        return [
            'order_id' => Order::factory(),
            'product_id' => Product::factory()->state([
                'kind' => $kind,
                'price_amount' => $price,
            ]),
            'product_kind' => $kind,
            'product_name_snapshot' => fake()->randomElement(['باقة متميزة', 'كتاب مراجعة', 'منتج رقمي']),
            'quantity' => 1,
            'unit_price_amount' => $price,
            'total_price_amount' => $price,
            'meta' => null,
        ];
    }
}
