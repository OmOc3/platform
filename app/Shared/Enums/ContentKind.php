<?php

namespace App\Shared\Enums;

enum ContentKind: string
{
    case Lecture = 'lecture';
    case Review = 'review';
    case Summary = 'summary';
    case Exam = 'exam';
}
