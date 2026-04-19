<?php

namespace App\Shared\Enums;

enum EntitlementSource: string
{
    case DirectPurchase = 'direct_purchase';
    case PackagePurchase = 'package_purchase';
    case AdminGrant = 'admin_grant';
    case CodeRedemption = 'code_redemption';
    case Free = 'free';
}
