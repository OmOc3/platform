<?php

namespace App\Modules\Students\Actions\Profiles;

use App\Modules\Students\Models\Student;
use App\Shared\Contracts\AuditLogger;

class UpdateStudentProfileAction
{
    public function __construct(private readonly AuditLogger $auditLogger)
    {
    }

    public function execute(Student $student, array $data): Student
    {
        $oldValues = $student->only([
            'name',
            'email',
            'phone',
            'parent_phone',
            'governorate',
        ]);

        $student->fill($data);
        $student->save();

        $this->auditLogger->log(
            event: 'students.profile.updated',
            actor: $student,
            subject: $student,
            oldValues: $oldValues,
            newValues: $student->fresh()->only(array_keys($oldValues)),
        );

        return $student->refresh();
    }
}
