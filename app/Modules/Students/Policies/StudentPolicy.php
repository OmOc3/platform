<?php

namespace App\Modules\Students\Policies;

use App\Modules\Identity\Models\Admin;
use App\Modules\Students\Models\Student;

class StudentPolicy
{
    public function viewAny(Admin $admin): bool
    {
        return $admin->hasPermissionTo('students.view', 'admin');
    }

    public function update(Admin $admin, Student $student): bool
    {
        return $admin->hasPermissionTo('students.manage', 'admin');
    }

    public function viewProfile(Student $actor, Student $student): bool
    {
        return $actor->is($student);
    }

    public function updateProfile(Student $actor, Student $student): bool
    {
        return $actor->is($student);
    }
}
