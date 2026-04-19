<?php

namespace App\Modules\Commerce\Actions\Cart;

use App\Modules\Commerce\Models\CartItem;
use App\Shared\Enums\ProductKind;

class UpdateCartItemQuantityAction
{
    public function execute(CartItem $item, int $quantity): CartItem
    {
        $quantity = $item->product?->kind === ProductKind::Book
            ? max(1, min($quantity, 10))
            : 1;

        $item->update([
            'quantity' => $quantity,
            'total_price_amount' => $quantity * $item->unit_price_amount,
        ]);

        return $item->fresh('product');
    }
}
