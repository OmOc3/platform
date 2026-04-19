<?php

namespace App\Shared\Enums;

enum OrderStatus: string
{
    case Draft = 'draft';
    case PendingPayment = 'pending_payment';
    case Paid = 'paid';
    case Fulfilled = 'fulfilled';
    case Cancelled = 'cancelled';
    case Refunded = 'refunded';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'مسودة',
            self::PendingPayment => 'بانتظار السداد',
            self::Paid => 'مدفوع',
            self::Fulfilled => 'مفعّل',
            self::Cancelled => 'ملغي',
            self::Refunded => 'مرتجع',
        };
    }

    public function tone(): string
    {
        return match ($this) {
            self::Draft => 'neutral',
            self::PendingPayment => 'warning',
            self::Paid, self::Fulfilled => 'success',
            self::Cancelled => 'danger',
            self::Refunded => 'warning',
        };
    }
}
