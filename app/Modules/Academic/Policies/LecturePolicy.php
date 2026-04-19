<?php

namespace App\Modules\Academic\Policies;

use App\Modules\Academic\Models\Lecture;
use App\Modules\Identity\Models\Admin;

class LecturePolicy
{
    public function viewAny(Admin $admin): bool
    {
        return $admin->can('content.view');
    }

    public function view(Admin $admin, Lecture $lecture): bool
    {
        return $admin->can('content.view');
    }

    public function create(Admin $admin): bool
    {
        return $admin->can('content.manage');
    }

    public function update(Admin $admin, Lecture $lecture): bool
    {
        return $admin->can('content.manage');
    }

    public function delete(Admin $admin, Lecture $lecture): bool
    {
        return $admin->can('content.manage');
    }
}
