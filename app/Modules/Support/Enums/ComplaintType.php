<?php

namespace App\Modules\Support\Enums;

enum ComplaintType: string
{
    case Complaint = 'complaint';
    case Suggestion = 'suggestion';

    public function label(): string
    {
        return match ($this) {
            self::Complaint => 'شكوى',
            self::Suggestion => 'اقتراح',
        };
    }
}
