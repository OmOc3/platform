<?php

namespace App\Modules\Academic\Actions\Grades;

use App\Modules\Academic\Models\Grade;
use App\Shared\Contracts\AuditLogger;

class CreateGradeAction
{
    public function __construct(private readonly AuditLogger $auditLogger)
    {
    }

    public function execute(array $data, mixed $actor): Grade
    {
        $grade = Grade::query()->create($data);

        $this->auditLogger->log(
            event: 'academic.grade.created',
            actor: $actor,
            subject: $grade,
            newValues: $grade->fresh()->toArray(),
        );

        return $grade;
    }
}
