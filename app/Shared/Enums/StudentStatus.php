<?php

namespace App\Shared\Enums;

enum StudentStatus: string
{
    case Pending = 'pending';
    case Subscribed = 'subscribed';
    case Refused = 'refused';
    case Blocked = 'blocked';
}
