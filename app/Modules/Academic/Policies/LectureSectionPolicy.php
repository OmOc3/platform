<?php

namespace App\Modules\Academic\Policies;

use App\Modules\Academic\Models\LectureSection;
use App\Modules\Identity\Models\Admin;

class LectureSectionPolicy
{
    public function viewAny(Admin $admin): bool
    {
        return $admin->can('content.view');
    }

    public function view(Admin $admin, LectureSection $section): bool
    {
        return $admin->can('content.view');
    }

    public function create(Admin $admin): bool
    {
        return $admin->can('content.manage');
    }

    public function update(Admin $admin, LectureSection $section): bool
    {
        return $admin->can('content.manage');
    }

    public function delete(Admin $admin, LectureSection $section): bool
    {
        return $admin->can('content.manage');
    }
}
