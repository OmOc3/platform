<?php

namespace App\Modules\Commerce\Policies;

use App\Modules\Commerce\Models\Entitlement;
use App\Modules\Identity\Models\Admin;

class EntitlementPolicy
{
    public function viewAny(Admin $admin): bool
    {
        return $admin->can('entitlements.view');
    }

    public function view(Admin $admin, Entitlement $entitlement): bool
    {
        return $admin->can('entitlements.view');
    }

    public function create(Admin $admin): bool
    {
        return $admin->can('entitlements.manage');
    }

    public function update(Admin $admin, Entitlement $entitlement): bool
    {
        return $admin->can('entitlements.manage');
    }

    public function delete(Admin $admin, Entitlement $entitlement): bool
    {
        return $admin->can('entitlements.manage');
    }
}
