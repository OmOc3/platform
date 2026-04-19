<?php

namespace App\Modules\Academic\Policies;

use App\Modules\Academic\Models\Track;
use App\Modules\Identity\Models\Admin;

class TrackPolicy
{
    public function viewAny(Admin $admin): bool
    {
        return $admin->hasPermissionTo('tracks.view', 'admin');
    }

    public function view(Admin $admin, Track $track): bool
    {
        return $admin->hasPermissionTo('tracks.view', 'admin');
    }

    public function create(Admin $admin): bool
    {
        return $admin->hasPermissionTo('tracks.manage', 'admin');
    }

    public function update(Admin $admin, Track $track): bool
    {
        return $admin->hasPermissionTo('tracks.manage', 'admin');
    }

    public function delete(Admin $admin, Track $track): bool
    {
        return $admin->hasPermissionTo('tracks.manage', 'admin');
    }
}
