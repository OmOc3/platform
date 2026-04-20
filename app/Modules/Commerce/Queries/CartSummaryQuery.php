<?php

namespace App\Modules\Commerce\Queries;

use App\Modules\Commerce\Models\Cart;
use App\Modules\Students\Models\Student;
use App\Shared\Contracts\ShippingFeeCalculator;
use App\Shared\Enums\ProductKind;

class CartSummaryQuery
{
    public function __construct(private readonly ShippingFeeCalculator $shippingFeeCalculator)
    {
    }

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
        $needsShipping = $bookItems->isNotEmpty();
        $shipping = $this->shippingFeeCalculator->calculate($student, $bookItems);
        $shippingFeeAmount = $needsShipping ? $shipping['amount'] : 0;
        $grandTotal = $cart->items->sum('total_price_amount');

        return [
            'student' => $student,
            'cart' => $cart,
            'digitalItems' => $digitalItems,
            'bookItems' => $bookItems,
            'digitalTotal' => $digitalItems->sum('total_price_amount'),
            'bookTotal' => $bookItems->sum('total_price_amount'),
            'grandTotal' => $grandTotal,
            'shipping' => [
                'needs_shipping' => $needsShipping,
                'fee_amount' => $shippingFeeAmount,
                'fee_label' => $needsShipping ? $shipping['label'] : 'لا يوجد',
                'supported_governorates' => $shipping['supported_governorates'],
                'warning' => $shipping['warning'],
                'can_deliver' => $shipping['can_deliver'],
            ],
            'finalTotal' => $grandTotal + $shippingFeeAmount,
        ];
    }
}
