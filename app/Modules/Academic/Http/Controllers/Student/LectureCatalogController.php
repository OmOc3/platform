<?php

namespace App\Modules\Academic\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Modules\Academic\Models\Exam;
use App\Modules\Academic\Models\Lecture;
use App\Modules\Academic\Queries\StudentLectureCatalogQuery;
use App\Shared\Contracts\AccessResolver;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class LectureCatalogController extends Controller
{
    public function __construct(
        private readonly StudentLectureCatalogQuery $studentLectureCatalogQuery,
        private readonly AccessResolver $accessResolver,
    ) {
    }

    public function index(Request $request): View
    {
        $student = auth('student')->user();

        return view('student.catalog.lectures.index', $this->studentLectureCatalogQuery->dataFor($student, $request));
    }

    public function showLecture(Lecture $lecture): View
    {
        abort_unless($lecture->is_active, 404);

        $student = auth('student')->user();

        return view('student.catalog.lectures.show', [
            'lecture' => $lecture->load(['grade', 'track', 'curriculumSection', 'lectureSection', 'product']),
            'access' => $this->accessResolver->resolveState($student, $lecture),
        ]);
    }

    public function showExam(Exam $exam): View
    {
        abort_unless($exam->is_active, 404);

        $student = auth('student')->user();

        return view('student.catalog.exams.show', [
            'exam' => $exam->load(['grade', 'track', 'lecture']),
            'access' => $this->accessResolver->resolveState($student, $exam),
        ]);
    }
}
