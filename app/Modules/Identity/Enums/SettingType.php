<?php

namespace App\Modules\Identity\Enums;

enum SettingType: string
{
    case String = 'string';
    case Text = 'text';
    case Boolean = 'boolean';
    case Number = 'number';
    case Json = 'json';
}
