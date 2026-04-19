<?php

namespace App\Modules\Commerce\Enums;

enum BookAvailability: string
{
    case InStock = 'in_stock';
    case PreOrder = 'pre_order';
    case SoldOut = 'sold_out';
}
