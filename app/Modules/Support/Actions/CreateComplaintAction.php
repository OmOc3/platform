<?php

namespace App\Modules\Support\Actions;

use App\Modules\Students\Models\Student;
use App\Modules\Support\Models\Complaint;
use App\Shared\Contracts\AuditLogger;

class CreateComplaintAction
{
    public function __construct(private readonly AuditLogger $auditLogger)
    {
    }

    public function execute(Student $student, array $data): Complaint
    {
        $complaint = Complaint::query()->create([
            ...$data,
            'student_id' => $student->id,
            'status' => 'open',
        ]);

        $this->auditLogger->log(
            event: 'support.complaint.created',
            actor: $student,
            subject: $complaint,
            newValues: $complaint->toArray(),
        );

        return $complaint->refresh();
    }
}
