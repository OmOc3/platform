<?php

namespace App\Modules\Academic\Policies;

use App\Modules\Academic\Models\Exam;
use App\Modules\Identity\Models\Admin;

class ExamPolicy
{
    public function viewAny(Admin $admin): bool
    {
        return $admin->can('exams.view');
    }

    public function view(Admin $admin, Exam $exam): bool
    {
        return $admin->can('exams.view');
    }

    public function create(Admin $admin): bool
    {
        return $admin->can('exams.manage');
    }

    public function update(Admin $admin, Exam $exam): bool
    {
        return $admin->can('exams.manage');
    }

    public function delete(Admin $admin, Exam $exam): bool
    {
        return $admin->can('exams.manage');
    }
}
