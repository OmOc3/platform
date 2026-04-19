<?php

namespace App\Modules\Commerce\Queries;

use App\Modules\Commerce\Models\Cart;
use App\Modules\Students\Models\Student;
use App\Shared\Enums\ProductKind;

class CartSummaryQuery
{
    /**
     * @return array<string, mixed>
     */
    public function dataFor(Student $student): array
    {
        $cart = Cart::query()
            ->with(['items.product.book', 'items.product.package'])
            ->firstOrCreate(
                ['student_id' => $student->id],
                ['currency' => 'EGP'],
            )
            ->load(['items.product.book', 'items.product.package']);

        $digitalItems = $cart->items->filter(fn ($item) => $item->product?->kind !== ProductKind::Book);
        $bookItems = $cart->items->filter(fn ($item) => $item->product?->kind === ProductKind::Book);

        return [
            'cart' => $cart,
            'digitalItems' => $digitalItems,
            'bookItems' => $bookItems,
            'digitalTotal' => $digitalItems->sum('total_price_amount'),
            'bookTotal' => $bookItems->sum('total_price_amount'),
            'grandTotal' => $cart->items->sum('total_price_amount'),
        ];
    }
}
