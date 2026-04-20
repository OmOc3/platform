<?php

namespace App\Shared\Enums;

enum ShipmentStatus: string
{
    case Pending = 'pending';
    case Prepared = 'prepared';
    case HandedToCarrier = 'handed_to_carrier';
    case InTransit = 'in_transit';
    case Delivered = 'delivered';
    case Returned = 'returned';
    case Canceled = 'canceled';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'قيد المراجعة',
            self::Prepared => 'جاهز للشحن',
            self::HandedToCarrier => 'سُلّم للناقل',
            self::InTransit => 'في الطريق',
            self::Delivered => 'تم التسليم',
            self::Returned => 'مرتجع',
            self::Canceled => 'ملغي',
        };
    }

    public function tone(): string
    {
        return match ($this) {
            self::Pending, self::Prepared, self::HandedToCarrier, self::InTransit => 'warning',
            self::Delivered => 'success',
            self::Returned, self::Canceled => 'danger',
        };
    }
}
