<?php

namespace App\Modules\Students\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Modules\Students\Queries\StudentDashboardQuery;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function __construct(private readonly StudentDashboardQuery $studentDashboardQuery)
    {
    }

    public function __invoke(): View
    {
        $student = auth('student')->user();

        return view('student.dashboard.index', [
            'student' => $student,
            ...$this->studentDashboardQuery->dataFor($student),
        ]);
    }
}
