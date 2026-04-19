<?php

namespace App\Modules\Students\Actions\Admin;

use App\Modules\Students\Models\Student;
use App\Modules\Students\Models\StudentStatusHistory;
use App\Shared\Enums\StudentStatus;

class RecordStudentStatusHistoryAction
{
    public function execute(
        Student $student,
        ?StudentStatus $previousStatus,
        StudentStatus $newStatus,
        mixed $actor = null,
        ?string $reason = null,
    ): StudentStatusHistory {
        return StudentStatusHistory::query()->create([
            'student_id' => $student->id,
            'previous_status' => $previousStatus,
            'new_status' => $newStatus,
            'reason' => $reason,
            'actor_type' => $actor instanceof \Illuminate\Database\Eloquent\Model ? $actor->getMorphClass() : null,
            'actor_id' => $actor instanceof \Illuminate\Database\Eloquent\Model ? $actor->getKey() : null,
            'created_at' => now(),
        ]);
    }
}
