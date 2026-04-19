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

        return view('student.history.attendance', [
            'attendanceRecords' => $this->studentAttendanceHistoryQuery->builder($student)->paginate(12),
        ]);
    }
}
