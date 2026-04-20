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
        $supportedGovernorates = $bookItems
            ->flatMap(fn ($item) => (array) data_get($item->product?->book?->metadata, 'governorates', []))
            ->filter()
            ->unique()
            ->values();
        $needsShipping = $bookItems->isNotEmpty();
        $hasGovernorate = filled($student->governorate);
        $canDeliverToStudent = ! $needsShipping
            || $supportedGovernorates->isEmpty()
            || ($hasGovernorate && $supportedGovernorates->contains($student->governorate));
        $shippingWarning = match (true) {
            ! $needsShipping => null,
            ! $hasGovernorate => 'أضف المحافظة ورقم الهاتف قبل تجهيز طلب الكتب حتى يتمكن فريق الشحن من مراجعته.',
            ! $canDeliverToStudent => 'بعض الكتب الحالية لا تدعم الشحن إلى المحافظة المسجلة في حسابك. يمكنك تعديل بيانات الاستلام أو التواصل مع الدعم.',
            default => 'رسوم الشحن النهائية غير ممثلة كقاعدة مستقلة داخل النظام الحالي، لذلك تظهر المراجعة اللوجستية أولًا قبل اعتماد الطلب.',
        };
        $shippingFeeAmount = 0;
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
                'fee_label' => $needsShipping ? 'يحدد بعد التأكيد' : 'لا يوجد',
                'supported_governorates' => $supportedGovernorates,
                'warning' => $shippingWarning,
                'can_deliver' => $canDeliverToStudent,
            ],
            'finalTotal' => $grandTotal + $shippingFeeAmount,
        ];
    }
}
