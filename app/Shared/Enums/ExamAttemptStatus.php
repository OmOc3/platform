<?php

namespace App\Shared\Enums;

enum ExamAttemptStatus: string
{
    case InProgress = 'in_progress';
    case Submitted = 'submitted';
    case Graded = 'graded';

    public function label(): string
    {
        return match ($this) {
            self::InProgress => 'قيد الحل',
            self::Submitted => 'تم الإرسال',
            self::Graded => 'تم التصحيح',
        };
    }

    public function tone(): string
    {
        return match ($this) {
            self::InProgress => 'warning',
            self::Submitted => 'neutral',
            self::Graded => 'success',
        };
    }
}
