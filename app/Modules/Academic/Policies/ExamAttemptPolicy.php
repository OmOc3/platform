<?php

namespace App\Modules\Academic\Policies;

use App\Modules\Academic\Models\ExamAttempt;
use App\Modules\Identity\Models\Admin;
use App\Modules\Students\Models\Student;

class ExamAttemptPolicy
{
    public function viewAny(Admin|Student $user): bool
    {
        return $user instanceof Admin ? $user->can('exams.view') : false;
    }

    public function view(Admin|Student $user, ExamAttempt $examAttempt): bool
    {
        if ($user instanceof Admin) {
            return $user->can('exams.view');
        }

        return $examAttempt->student_id === $user->id;
    }

    public function update(Admin|Student $user, ExamAttempt $examAttempt): bool
    {
        if ($user instanceof Admin) {
            return $user->can('exams.manage');
        }

        return $examAttempt->student_id === $user->id;
    }
}
