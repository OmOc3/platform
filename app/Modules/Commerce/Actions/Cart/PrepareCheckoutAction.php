<?php

namespace App\Modules\Commerce\Actions\Cart;

use App\Modules\Commerce\Models\Cart;
use App\Modules\Students\Models\Student;
use App\Shared\Contracts\CheckoutService;
use App\Shared\Enums\ProductKind;

class PrepareCheckoutAction
{
    public function __construct(private readonly CheckoutService $checkoutService)
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function execute(Student $student): array
    {
        $cart = Cart::query()
            ->with(['items.product'])
            ->firstOrCreate(
                ['student_id' => $student->id],
                ['currency' => 'EGP'],
            )
            ->load('items.product');

        $digitalItems = $cart->items->filter(fn ($item) => $item->product?->kind !== ProductKind::Book)->values();
        $bookItems = $cart->items->filter(fn ($item) => $item->product?->kind === ProductKind::Book)->values();

        return [
            'digitalOrder' => $this->checkoutService->beginDigitalCheckout([
                'student' => $student,
                'items' => $digitalItems,
            ]),
            'bookOrder' => $this->checkoutService->beginBookCheckout([
                'student' => $student,
                'items' => $bookItems,
            ]),
        ];
    }
}
