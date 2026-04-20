<?php

namespace App\Shared\Enums;

enum AttendanceStatus: string
{
    case Present = 'present';
    case Absent = 'absent';
    case Excused = 'excused';
    case Late = 'late';

    public function label(): string
    {
        return match ($this) {
            self::Present => 'حاضر',
            self::Absent => 'غائب',
            self::Excused => 'بعذر',
            self::Late => 'متأخر',
        };
    }

    public function tone(): string
    {
        return match ($this) {
            self::Present => 'success',
            self::Absent => 'danger',
            self::Excused => 'warning',
            self::Late => 'warning',
        };
    }
}
