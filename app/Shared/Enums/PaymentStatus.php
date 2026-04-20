<?php

namespace App\Shared\Enums;

enum PaymentStatus: string
{
    case Pending = 'pending';
    case RequiresAction = 'requires_action';
    case Paid = 'paid';
    case Failed = 'failed';
    case Canceled = 'canceled';
    case Refunded = 'refunded';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'قيد الإنشاء',
            self::RequiresAction => 'بحاجة إلى متابعة',
            self::Paid => 'تم السداد',
            self::Failed => 'فشل السداد',
            self::Canceled => 'ملغي',
            self::Refunded => 'مرتجع',
        };
    }

    public function tone(): string
    {
        return match ($this) {
            self::Pending, self::RequiresAction => 'warning',
            self::Paid => 'success',
            self::Failed, self::Canceled => 'danger',
            self::Refunded => 'neutral',
        };
    }
}
