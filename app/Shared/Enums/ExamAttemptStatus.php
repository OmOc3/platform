<?php

namespace App\Shared\Enums;

enum ExamAttemptStatus: string
{
    case Draft = 'draft';
    case InProgress = 'in_progress';
    case Submitted = 'submitted';
    case Reviewed = 'reviewed';
    case Blocked = 'blocked';
}
