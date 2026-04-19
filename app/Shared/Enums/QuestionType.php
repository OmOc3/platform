<?php

namespace App\Shared\Enums;

enum QuestionType: string
{
    case MultipleChoice = 'multiple_choice';

    public function label(): string
    {
        return match ($this) {
            self::MultipleChoice => 'اختيار من متعدد',
        };
    }
}
