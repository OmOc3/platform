<?php

namespace App\Modules\Academic\Policies;

use App\Modules\Academic\Models\CurriculumSection;
use App\Modules\Identity\Models\Admin;

class CurriculumSectionPolicy
{
    public function viewAny(Admin $admin): bool
    {
        return $admin->can('content.view');
    }

    public function view(Admin $admin, CurriculumSection $section): bool
    {
        return $admin->can('content.view');
    }

    public function create(Admin $admin): bool
    {
        return $admin->can('content.manage');
    }

    public function update(Admin $admin, CurriculumSection $section): bool
    {
        return $admin->can('content.manage');
    }

    public function delete(Admin $admin, CurriculumSection $section): bool
    {
        return $admin->can('content.manage');
    }
}
