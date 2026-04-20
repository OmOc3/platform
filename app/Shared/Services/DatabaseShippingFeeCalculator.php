<?php

namespace App\Shared\Services;

use App\Modules\Students\Models\Student;
use App\Shared\Contracts\ShippingFeeCalculator;
use Illuminate\Support\Collection;

class DatabaseShippingFeeCalculator implements ShippingFeeCalculator
{
    public function calculate(Student $student, Collection $items, array $address = []): array
    {
        $supportedGovernorates = $items
            ->flatMap(fn ($item) => (array) data_get($item, 'product.book.metadata.governorates', []))
            ->filter()
            ->unique()
            ->values();

        if ($items->isEmpty()) {
            return [
                'amount' => 0,
                'label' => 'لا يوجد',
                'warning' => null,
                'can_deliver' => true,
                'supported_governorates' => $supportedGovernorates,
            ];
        }

        $governorate = trim((string) ($address['governorate'] ?? $student->governorate ?? ''));
        $city = trim((string) ($address['city'] ?? ''));
        $hasGovernorate = $governorate !== '';
        $canDeliver = $supportedGovernorates->isEmpty() || ($hasGovernorate && $supportedGovernorates->contains($governorate));

        $warning = match (true) {
            ! $hasGovernorate => 'أضف المحافظة قبل بدء دفع طلب الكتب حتى يتمكن فريق الشحن من المراجعة.',
            ! $canDeliver => 'المحافظة المحددة غير مدعومة لبعض الكتب الموجودة في السلة الحالية.',
            $city === '' => 'أضف المدينة أو المنطقة لتسهيل تجهيز الشحنة قبل التسليم.',
            default => null,
        };

        $amount = match ($governorate) {
            'القاهرة', 'الجيزة' => (int) config('services.commerce.shipping.fees.cairo', 35),
            'الإسكندرية' => (int) config('services.commerce.shipping.fees.alexandria', 45),
            default => (int) config('services.commerce.shipping.fees.default', 60),
        };

        return [
            'amount' => $canDeliver ? $amount : 0,
            'label' => $canDeliver ? 'رسوم شحن تقديرية' : 'غير متاح',
            'warning' => $warning,
            'can_deliver' => $canDeliver,
            'supported_governorates' => $supportedGovernorates,
        ];
    }
}
