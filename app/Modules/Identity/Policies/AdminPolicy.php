<?php

namespace App\Modules\Identity\Policies;

use App\Modules\Identity\Models\Admin;

class AdminPolicy
{
    public function viewAny(Admin $admin): bool
    {
        return $admin->hasPermissionTo('admins.view', 'admin');
    }

    public function view(Admin $admin, Admin $model): bool
    {
        return $admin->hasPermissionTo('admins.view', 'admin');
    }

    public function create(Admin $admin): bool
    {
        return $admin->hasPermissionTo('admins.manage', 'admin');
    }

    public function update(Admin $admin, Admin $model): bool
    {
        return $admin->hasPermissionTo('admins.manage', 'admin');
    }

    public function delete(Admin $admin, Admin $model): bool
    {
        return $admin->hasPermissionTo('admins.manage', 'admin') && ! $admin->is($model);
    }
}
