<?php

namespace App\Modules\Academic\Actions\Grades;

use App\Modules\Academic\Models\Grade;
use App\Shared\Contracts\AuditLogger;

class UpdateGradeAction
{
    public function __construct(private readonly AuditLogger $auditLogger)
    {
    }

    public function execute(Grade $grade, array $data, mixed $actor): Grade
    {
        $oldValues = $grade->toArray();

        $grade->update($data);

        $this->auditLogger->log(
            event: 'academic.grade.updated',
            actor: $actor,
            subject: $grade,
            oldValues: $oldValues,
            newValues: $grade->fresh()->toArray(),
        );

        return $grade->refresh();
    }
}
