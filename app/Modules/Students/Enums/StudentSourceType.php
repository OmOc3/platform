<?php

namespace App\Modules\Students\Enums;

enum StudentSourceType: string
{
    case Online = 'online';
    case Center = 'center';
    case Hybrid = 'hybrid';
}
