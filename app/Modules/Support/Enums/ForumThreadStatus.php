<?php

namespace App\Modules\Support\Enums;

enum ForumThreadStatus: string
{
    case Open = 'open';
    case Answered = 'answered';
    case Closed = 'closed';
}
