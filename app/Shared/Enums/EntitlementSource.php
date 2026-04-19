<?php

namespace App\Shared\Enums;

enum EntitlementSource: string
{
    case DirectPurchase = 'direct_purchase';
    case PackagePurchase = 'package_purchase';
    case AdminGrant = 'admin_grant';
    case CodeRedemption = 'code_redemption';
    case Free = 'free';

    public function label(): string
    {
        return match ($this) {
            self::DirectPurchase => 'شراء مباشر',
            self::PackagePurchase => 'شراء باقة',
            self::AdminGrant => 'منحة إدارية',
            self::CodeRedemption => 'كود تفعيل',
            self::Free => 'محتوى مجاني',
        };
    }
}
