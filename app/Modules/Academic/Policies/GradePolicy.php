<?php

namespace App\Modules\Academic\Policies;

use App\Modules\Academic\Models\Grade;
use App\Modules\Identity\Models\Admin;

class GradePolicy
{
    public function viewAny(Admin $admin): bool
    {
        return $admin->hasPermissionTo('grades.view', 'admin');
    }

    public function view(Admin $admin, Grade $grade): bool
    {
        return $admin->hasPermissionTo('grades.view', 'admin');
    }

    public function create(Admin $admin): bool
    {
        return $admin->hasPermissionTo('grades.manage', 'admin');
    }

    public function update(Admin $admin, Grade $grade): bool
    {
        return $admin->hasPermissionTo('grades.manage', 'admin');
    }

    public function delete(Admin $admin, Grade $grade): bool
    {
        return $admin->hasPermissionTo('grades.manage', 'admin');
    }
}
