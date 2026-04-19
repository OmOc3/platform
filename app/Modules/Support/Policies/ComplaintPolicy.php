<?php

namespace App\Modules\Support\Policies;

use App\Modules\Students\Models\Student;
use App\Modules\Support\Models\Complaint;

class ComplaintPolicy
{
    public function viewAny(Student $student): bool
    {
        return true;
    }

    public function create(Student $student): bool
    {
        return true;
    }

    public function view(Student $student, Complaint $complaint): bool
    {
        return $complaint->student_id === $student->id;
    }
}
