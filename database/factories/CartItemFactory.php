<?php

namespace Database\Factories;

use App\Modules\Commerce\Models\Cart;
use App\Modules\Commerce\Models\CartItem;
use App\Modules\Commerce\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CartItem>
 */
class CartItemFactory extends Factory
{
    protected $model = CartItem::class;

    public function definition(): array
    {
        $unitPrice = fake()->numberBetween(50, 250);

        return [
            'cart_id' => Cart::factory(),
            'product_id' => Product::factory(),
            'quantity' => 1,
            'unit_price_amount' => $unitPrice,
            'total_price_amount' => $unitPrice,
            'meta' => null,
        ];
    }
}
