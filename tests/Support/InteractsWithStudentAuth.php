<?php

namespace Tests\Support;

use App\Modules\Students\Models\Student;
use App\Shared\Enums\StudentStatus;

trait InteractsWithStudentAuth
{
    protected function signInStudent(array $attributes = []): Student
    {
        $student = Student::factory()->create([
            'status' => StudentStatus::Pending,
            ...$attributes,
        ]);

        $this->actingAs($student, 'student');

        return $student;
    }
}
