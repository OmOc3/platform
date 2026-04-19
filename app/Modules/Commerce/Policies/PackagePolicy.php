<?php

namespace App\Modules\Commerce\Policies;

use App\Modules\Commerce\Models\Package;
use App\Modules\Identity\Models\Admin;

class PackagePolicy
{
    public function viewAny(Admin $admin): bool
    {
        return $admin->can('packages.view');
    }

    public function view(Admin $admin, Package $package): bool
    {
        return $admin->can('packages.view');
    }

    public function create(Admin $admin): bool
    {
        return $admin->can('packages.manage');
    }

    public function update(Admin $admin, Package $package): bool
    {
        return $admin->can('packages.manage');
    }

    public function delete(Admin $admin, Package $package): bool
    {
        return $admin->can('packages.manage');
    }
}
