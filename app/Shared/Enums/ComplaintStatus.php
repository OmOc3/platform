<?php

namespace App\Shared\Enums;

enum ComplaintStatus: string
{
    case Open = 'open';
    case UnderReview = 'under_review';
    case Resolved = 'resolved';
    case Closed = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::Open => 'مفتوحة',
            self::UnderReview => 'قيد المتابعة',
            self::Resolved => 'تم حلها',
            self::Closed => 'مغلقة',
        };
    }

    public function tone(): string
    {
        return match ($this) {
            self::Open => 'warning',
            self::UnderReview => 'neutral',
            self::Resolved => 'success',
            self::Closed => 'danger',
        };
    }
}
