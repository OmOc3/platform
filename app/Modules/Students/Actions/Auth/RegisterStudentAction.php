<?php

namespace App\Modules\Students\Actions\Auth;

use App\Modules\Students\Actions\Admin\AssignStudentNumberAction;
use App\Modules\Students\Actions\Admin\RecordStudentStatusHistoryAction;
use App\Modules\Students\Enums\StudentSourceType;
use App\Modules\Students\Models\Student;
use App\Shared\Contracts\AuditLogger;
use App\Shared\Enums\StudentStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RegisterStudentAction
{
    public function __construct(
        private readonly AssignStudentNumberAction $assignStudentNumberAction,
        private readonly RecordStudentStatusHistoryAction $recordStudentStatusHistoryAction,
        private readonly AuditLogger $auditLogger,
    ) {
    }

    public function execute(array $data): Student
    {
        return DB::transaction(function () use ($data): Student {
            $student = Student::query()->create([
                ...$data,
                'uuid' => (string) Str::uuid(),
                'student_number' => null,
                'status' => StudentStatus::Pending,
                'source_type' => $data['source_type'] ?? StudentSourceType::Online,
                'center_id' => null,
                'group_id' => null,
                'owner_admin_id' => null,
                'notes' => null,
                'language' => 'ar',
                'last_login_at' => null,
            ]);

            $student = $this->assignStudentNumberAction->execute($student);

            $this->recordStudentStatusHistoryAction->execute(
                student: $student,
                previousStatus: null,
                newStatus: StudentStatus::Pending,
                actor: null,
                reason: 'self_registration',
            );

            $this->auditLogger->log(
                event: 'students.registered',
                actor: $student,
                subject: $student,
                newValues: $student->toArray(),
            );

            return $student;
        });
    }
}
