<?php

namespace App\Shared\Enums;

enum OrderKind: string
{
    case Digital = 'digital';
    case Book = 'book';

    public function label(): string
    {
        return match ($this) {
            self::Digital => 'طلب رقمي',
            self::Book => 'طلب كتب',
        };
    }
}
