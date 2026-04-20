<?php

namespace App\Modules\Students\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Modules\Centers\Queries\StudentAttendanceHistoryQuery;
use Illuminate\Contracts\View\View;

class AttendanceHistoryController extends Controller
{
    public function __construct(private readonly StudentAttendanceHistoryQuery $studentAttendanceHistoryQuery)
    {
    }

    public function __invoke(): View
    {
        $student = auth('student')->user();
        $builder = $this->studentAttendanceHistoryQuery->builder($student);
        $summary = [
            'count' => (clone $builder)->count(),
            'present_count' => (clone $builder)->where('attendance_status', 'present')->count(),
            'late_count' => (clone $builder)->where('attendance_status', 'late')->count(),
            'average_score' => round((float) ((clone $builder)->whereNotNull('score')->avg('score') ?? 0), 1),
        ];

        return view('student.history.attendance', [
            'attendanceRecords' => $builder->paginate(12),
            'summary' => $summary,
        ]);
    }
}
