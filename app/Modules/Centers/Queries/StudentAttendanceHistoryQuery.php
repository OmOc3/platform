<?php

namespace App\Modules\Centers\Queries;

use App\Modules\Centers\Models\AttendanceRecord;
use App\Modules\Students\Models\Student;
use Illuminate\Database\Eloquent\Builder;

class StudentAttendanceHistoryQuery
{
    public function builder(Student $student): Builder
    {
        return AttendanceRecord::query()
            ->with(['session.group.center'])
            ->where('student_id', $student->id)
            ->latest('recorded_at');
    }
}
