<?php

namespace App\Modules\Students\Actions\Auth;

use App\Modules\Students\Models\Student;

class UpdateStudentLastLoginAction
{
    public function execute(Student $student): Student
    {
        $student->forceFill([
            'last_login_at' => now(),
        ])->save();

        return $student->refresh();
    }
}
