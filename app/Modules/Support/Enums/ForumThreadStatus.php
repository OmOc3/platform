<?php

namespace App\Modules\Support\Enums;

enum ForumThreadStatus: string
{
    case Open = 'open';
    case Answered = 'answered';
    case Closed = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::Open => 'مفتوح',
            self::Answered => 'تم الرد',
            self::Closed => 'مغلق',
        };
    }

    public function tone(): string
    {
        return match ($this) {
            self::Open => 'neutral',
            self::Answered => 'success',
            self::Closed => 'warning',
        };
    }
}
