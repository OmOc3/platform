<?php

namespace App\Shared\Enums;

enum ContentAccessState: string
{
    case Open = 'open';
    case Free = 'free';
    case Buy = 'buy';
    case IncludedInPackage = 'included_in_package';
    case OwnedViaEntitlement = 'owned_via_entitlement';
    case Unavailable = 'unavailable';
}
