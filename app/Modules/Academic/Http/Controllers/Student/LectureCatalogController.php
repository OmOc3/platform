<?php

namespace App\Modules\Academic\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Modules\Academic\Models\Exam;
use App\Modules\Academic\Models\ExamAttempt;
use App\Modules\Academic\Models\Lecture;
use App\Modules\Academic\Queries\StudentLectureCatalogQuery;
use App\Shared\Contracts\AccessResolver;
use App\Shared\Enums\ContentAccessState;
use App\Shared\Enums\ExamAttemptStatus;
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
        $exam->load(['grade', 'track', 'lecture', 'examQuestions.question.choices']);
        $access = $this->accessResolver->resolveState($student, $exam);
        $currentAttempt = ExamAttempt::query()
            ->where('exam_id', $exam->id)
            ->where('student_id', $student->id)
            ->where('status', ExamAttemptStatus::InProgress)
            ->latest('started_at')
            ->first();
        $latestAttempt = ExamAttempt::query()
            ->where('exam_id', $exam->id)
            ->where('student_id', $student->id)
            ->where('status', ExamAttemptStatus::Graded)
            ->latest('graded_at')
            ->first();
        $attemptsCount = ExamAttempt::query()
            ->where('exam_id', $exam->id)
            ->where('student_id', $student->id)
            ->count();
        $maxAttempts = max(1, (int) data_get($exam->metadata, 'max_attempts', 1));
        $hasExamAccess = in_array($access['state'], [
            ContentAccessState::Open,
            ContentAccessState::Free,
            ContentAccessState::OwnedViaEntitlement,
        ], true);
        $canStartAttempt = $hasExamAccess && $exam->examQuestions->isNotEmpty() && ($currentAttempt !== null || $attemptsCount < $maxAttempts);
        $attemptMessage = match (true) {
            ! $hasExamAccess => $access['reason'] ?: 'هذا الاختبار غير متاح لحسابك الآن.',
            $exam->examQuestions->isEmpty() => 'لم يتم تجهيز أسئلة هذا الاختبار بعد.',
            $currentAttempt instanceof ExamAttempt => 'لديك محاولة جارية ويمكنك استكمالها من حيث توقفت.',
            $attemptsCount >= $maxAttempts => 'تم استهلاك عدد المحاولات المسموح به لهذا الاختبار.',
            default => 'يمكنك بدء المحاولة الآن وسيتم إظهار النتيجة مباشرة بعد الإرسال.',
        };

        return view('student.catalog.exams.show', [
            'exam' => $exam,
            'access' => $access,
            'currentAttempt' => $currentAttempt,
            'latestAttempt' => $latestAttempt,
            'attemptsCount' => $attemptsCount,
            'maxAttempts' => $maxAttempts,
            'canStartAttempt' => $canStartAttempt,
            'attemptMessage' => $attemptMessage,
        ]);
    }
}
