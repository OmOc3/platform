<?php

namespace App\Modules\Commerce\Enums;

enum BookAvailability: string
{
    case InStock = 'in_stock';
    case PreOrder = 'pre_order';
    case SoldOut = 'sold_out';

    public function label(): string
    {
        return match ($this) {
            self::InStock => 'متاح الآن',
            self::PreOrder => 'حجز مسبق',
            self::SoldOut => 'نفد المخزون',
        };
    }

    public function tone(): string
    {
        return match ($this) {
            self::InStock => 'success',
            self::PreOrder => 'warning',
            self::SoldOut => 'danger',
        };
    }
}
