<?php

namespace App\Modules\Support\Policies;

use App\Modules\Identity\Models\Admin;
use App\Modules\Students\Models\Student;
use App\Modules\Support\Models\Complaint;

class ComplaintPolicy
{
    public function viewAny(Admin|Student $user): bool
    {
        return $user instanceof Admin ? $user->can('complaints.view') : true;
    }

    public function create(Student $student): bool
    {
        return true;
    }

    public function view(Admin|Student $user, Complaint $complaint): bool
    {
        if ($user instanceof Admin) {
            return $user->can('complaints.view');
        }

        return $complaint->student_id === $user->id;
    }

    public function update(Admin $admin, Complaint $complaint): bool
    {
        return $admin->can('complaints.manage');
    }
}
