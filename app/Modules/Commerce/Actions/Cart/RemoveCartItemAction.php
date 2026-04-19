<?php

namespace App\Modules\Commerce\Actions\Cart;

use App\Modules\Commerce\Models\CartItem;

class RemoveCartItemAction
{
    public function execute(CartItem $item): void
    {
        $item->delete();
    }
}
