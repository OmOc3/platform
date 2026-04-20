<?php

namespace App\Modules\Academic\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Modules\Academic\Models\Exam;
use App\Modules\Academic\Models\ExamAttempt;
use App\Modules\Academic\Models\Lecture;
use App\Modules\Academic\Queries\StudentLectureCatalogQuery;
use App\Shared\Contracts\AccessResolver;
use App\Shared\Contracts\LectureProgressService;
use App\Shared\Enums\ContentAccessState;
use App\Shared\Enums\ExamAttemptStatus;
use App\Shared\Enums\LectureAssetKind;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;

class LectureCatalogController extends Controller
{
    public function __construct(
        private readonly StudentLectureCatalogQuery $studentLectureCatalogQuery,
        private readonly AccessResolver $accessResolver,
        private readonly LectureProgressService $lectureProgressService,
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
        $lecture->load([
            'grade',
            'track',
            'curriculumSection',
            'lectureSection',
            'product',
            'assets' => fn ($query) => $query->where('is_active', true)->orderBy('sort_order')->orderBy('id'),
            'checkpoints' => fn ($query) => $query->orderBy('sort_order')->orderBy('id'),
            'exams' => fn ($query) => $query
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderByDesc('published_at')
                ->with(['attempts' => fn ($attempts) => $attempts
                    ->where('student_id', $student->id)
                    ->latest('started_at')]),
        ]);
        $access = $this->accessResolver->resolveState($student, $lecture);
        $canConsume = in_array($access['state'], [
            ContentAccessState::Open,
            ContentAccessState::Free,
            ContentAccessState::OwnedViaEntitlement,
        ], true);
        $progress = $canConsume ? $this->lectureProgressService->touchOpen($student, $lecture) : null;
        $primaryAsset = $canConsume ? $this->resolvePrimaryAsset($lecture->assets) : null;
        $supportingAssets = $canConsume && $primaryAsset
            ? $lecture->assets->reject(fn ($asset) => $asset->is($primaryAsset))->values()
            : collect();
        $relatedExams = $canConsume ? $this->relatedExamsForLecture($lecture, $student) : collect();

        return view('student.catalog.lectures.show', [
            'lecture' => $lecture,
            'access' => $access,
            'canConsume' => $canConsume,
            'progress' => $progress,
            'primaryAsset' => $primaryAsset,
            'supportingAssets' => $supportingAssets,
            'relatedExams' => $relatedExams,
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

    private function resolvePrimaryAsset(Collection $assets): mixed
    {
        $priority = [
            LectureAssetKind::EmbedVideo->value => 1,
            LectureAssetKind::ExternalVideo->value => 2,
            LectureAssetKind::TextBlock->value => 3,
            LectureAssetKind::AttachmentLink->value => 4,
            LectureAssetKind::ResourceLink->value => 5,
        ];

        return $assets
            ->sortBy(fn ($asset): array => [$priority[$asset->kind->value] ?? 99, $asset->sort_order, $asset->id])
            ->first();
    }

    private function relatedExamsForLecture(Lecture $lecture, mixed $student): Collection
    {
        return $lecture->exams
            ->map(function (Exam $exam) use ($student): array {
                $access = $this->accessResolver->resolveState($student, $exam);
                $attempts = $exam->attempts;
                $currentAttempt = $attempts->first(fn (ExamAttempt $attempt): bool => $attempt->status === ExamAttemptStatus::InProgress);
                $gradedAttempt = $attempts->first(fn (ExamAttempt $attempt): bool => $attempt->status === ExamAttemptStatus::Graded);

                $cta = match (true) {
                    $currentAttempt instanceof ExamAttempt => [
                        'label' => 'استكمل الاختبار',
                        'href' => route('student.exam-attempts.show', $currentAttempt),
                    ],
                    $gradedAttempt instanceof ExamAttempt => [
                        'label' => 'راجع النتيجة',
                        'href' => route('student.exam-attempts.result', $gradedAttempt),
                    ],
                    in_array($access['state'], [ContentAccessState::Open, ContentAccessState::Free, ContentAccessState::OwnedViaEntitlement], true) => [
                        'label' => 'ابدأ الاختبار',
                        'href' => route('student.lectures.exams.show', $exam),
                    ],
                    default => [
                        'label' => 'استعرض الاختبار',
                        'href' => route('student.lectures.exams.show', $exam),
                    ],
                };

                return [
                    'exam' => $exam,
                    'access' => $access,
                    'cta' => $cta,
                ];
            })
            ->values();
    }
}
