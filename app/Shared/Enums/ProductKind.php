<?php

namespace App\Shared\Enums;

enum ProductKind: string
{
    case Lecture = 'lecture';
    case Package = 'package';
    case Book = 'book';
    case Camp = 'camp';

    public function label(): string
    {
        return match ($this) {
            self::Lecture => 'محاضرة',
            self::Package => 'باقة',
            self::Book => 'كتاب',
            self::Camp => 'معسكر',
        };
    }
}
