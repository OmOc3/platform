<?php

namespace App\Shared\Enums;

enum StudentStatus: string
{
    case Pending = 'pending';
    case Subscribed = 'subscribed';
    case Refused = 'refused';
    case Blocked = 'blocked';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'قيد المراجعة',
            self::Subscribed => 'مشترك',
            self::Refused => 'مرفوض',
            self::Blocked => 'موقوف',
        };
    }
}
