<?php

namespace App\Modules\Students\Actions\Admin;

use App\Modules\Students\Models\Student;
use App\Shared\Contracts\AuditLogger;
use Illuminate\Support\Facades\DB;

class UpdateStudentByAdminAction
{
    public function __construct(
        private readonly RecordStudentStatusHistoryAction $recordStudentStatusHistoryAction,
        private readonly AuditLogger $auditLogger,
    ) {
    }

    public function execute(Student $student, array $data, mixed $actor): Student
    {
        return DB::transaction(function () use ($student, $data, $actor): Student {
            $oldValues = $student->toArray();
            $oldStatus = $student->status;

            $student->fill($data);
            $student->save();

            if ($oldStatus !== $student->status) {
                $this->recordStudentStatusHistoryAction->execute(
                    student: $student,
                    previousStatus: $oldStatus,
                    newStatus: $student->status,
                    actor: $actor,
                    reason: $data['status_reason'] ?? null,
                );
            }

            $this->auditLogger->log(
                event: 'students.admin.updated',
                actor: $actor,
                subject: $student,
                oldValues: $oldValues,
                newValues: $student->fresh()->toArray(),
            );

            return $student->refresh();
        });
    }
}
