<?php

namespace App\Modules\Students\Enums;

enum StudentSourceType: string
{
    case Online = 'online';
    case Center = 'center';
    case Hybrid = 'hybrid';

    public function label(): string
    {
        return match ($this) {
            self::Online => 'أونلاين',
            self::Center => 'سنتر',
            self::Hybrid => 'هجين',
        };
    }
}
