<?php

namespace App\Modules\Commerce\Actions\Cart;

use App\Modules\Commerce\Models\Cart;
use App\Modules\Commerce\Models\CartItem;
use App\Modules\Commerce\Models\Product;
use App\Modules\Students\Models\Student;
use App\Shared\Enums\ProductKind;

class AddProductToCartAction
{
    public function execute(Student $student, Product $product, int $quantity = 1): CartItem
    {
        $cart = Cart::query()->firstOrCreate(
            ['student_id' => $student->id],
            ['currency' => $product->currency],
        );

        $normalizedQuantity = $product->kind === ProductKind::Book
            ? max(1, min($quantity, 10))
            : 1;

        $item = $cart->items()->firstOrNew([
            'product_id' => $product->id,
        ]);

        $item->quantity = $product->kind === ProductKind::Book
            ? ($item->exists ? $item->quantity + $normalizedQuantity : $normalizedQuantity)
            : 1;
        $item->unit_price_amount = $product->price_amount;
        $item->total_price_amount = $item->quantity * $product->price_amount;
        $item->meta = [
            'product_kind' => $product->kind->value,
        ];
        $item->save();

        return $item->fresh('product');
    }
}
