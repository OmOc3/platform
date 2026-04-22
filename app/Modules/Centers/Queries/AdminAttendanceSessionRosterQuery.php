<?php

namespace App\Modules\Centers\Queries;

use App\Modules\Centers\Models\AttendanceSession;
use App\Modules\Students\Models\Student;
use Illuminate\Support\Collection;

class AdminAttendanceSessionRosterQuery
{
    /**
     * @return Collection<int, array{student: Student, record: mixed}>
     */
    public function items(AttendanceSession $attendanceSession): Collection
    {
        $attendanceSession->loadMissing(['group.students', 'records']);

        $recordsByStudent = $attendanceSession->records->keyBy('student_id');

        return $attendanceSession->group?->students
            ->sortBy('name')
            ->values()
            ->map(fn (Student $student): array => [
                'student' => $student,
                'record' => $recordsByStudent->get($student->id),
            ]) ?? collect();
    }
}
