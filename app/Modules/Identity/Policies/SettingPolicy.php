<?php

namespace App\Modules\Identity\Policies;

use App\Modules\Identity\Models\Admin;
use App\Modules\Identity\Models\Setting;

class SettingPolicy
{
    public function viewAny(Admin $admin): bool
    {
        return $admin->hasPermissionTo('settings.view', 'admin');
    }

    public function view(Admin $admin, Setting $setting): bool
    {
        return $admin->hasPermissionTo('settings.view', 'admin');
    }

    public function create(Admin $admin): bool
    {
        return $admin->hasPermissionTo('settings.manage', 'admin');
    }

    public function update(Admin $admin, Setting $setting): bool
    {
        return $admin->hasPermissionTo('settings.manage', 'admin');
    }

    public function delete(Admin $admin, Setting $setting): bool
    {
        return $admin->hasPermissionTo('settings.manage', 'admin');
    }
}
