<?php

namespace App\Modules\Students\Actions\Admin;

use App\Modules\Students\Models\Student;

class AssignStudentNumberAction
{
    public function execute(Student $student): Student
    {
        if ($student->student_number) {
            return $student;
        }

        $student->forceFill([
            'student_number' => sprintf('STU-%06d', $student->getKey()),
        ])->save();

        return $student->refresh();
    }
}
