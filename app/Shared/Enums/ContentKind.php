<?php

namespace App\Shared\Enums;

enum ContentKind: string
{
    case Lecture = 'lecture';
    case Review = 'review';
    case Summary = 'summary';
    case Exam = 'exam';

    public function label(): string
    {
        return match ($this) {
            self::Lecture => 'محاضرة',
            self::Review => 'مراجعة',
            self::Summary => 'ملخص',
            self::Exam => 'اختبار',
        };
    }
}
