<?php

namespace App\Modules\Commerce\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Modules\Academic\Models\Exam;
use App\Modules\Academic\Models\ExamAttempt;
use App\Modules\Academic\Models\Lecture;
use App\Modules\Commerce\Actions\Packages\EvaluatePackageEligibilityAction;
use App\Modules\Commerce\Models\CartItem;
use App\Modules\Commerce\Models\Package;
use App\Modules\Commerce\Models\PackageItem;
use App\Modules\Commerce\Queries\PackageCatalogQuery;
use App\Shared\Contracts\AccessResolver;
use App\Shared\Enums\ExamAttemptStatus;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class PackageCatalogController extends Controller
{
    public function __construct(
        private readonly PackageCatalogQuery $packageCatalogQuery,
        private readonly EvaluatePackageEligibilityAction $evaluatePackageEligibilityAction,
        private readonly AccessResolver $accessResolver,
    ) {
    }

    public function index(Request $request): View
    {
        $student = auth('student')->user();

        return view('student.catalog.packages.index', [
            'packages' => $this->packageCatalogQuery->paginateFor($student, $request),
        ]);
    }

    public function show(Package $package): View
    {
        $student = auth('student')->user();
        $package->load(['product', 'items.item']);
        $package->items->loadMorph('item', [
            Lecture::class => [
                'product',
                'lectureSection',
                'assets' => fn ($query) => $query
                    ->where('is_active', true)
                    ->orderBy('sort_order')
                    ->orderBy('id'),
                'exams' => fn ($query) => $query
                    ->where('is_active', true)
                    ->orderBy('sort_order')
                    ->orderByDesc('published_at')
                    ->with(['attempts' => fn ($attempts) => $attempts
                        ->where('student_id', $student->id)
                        ->latest('started_at')]),
            ],
        ]);

        $contentItems = $package->items
            ->filter(fn (PackageItem $item): bool => $item->item instanceof Lecture)
            ->map(function (PackageItem $item) use ($student, $package): array {
                /** @var Lecture $lecture */
                $lecture = $item->item;

                return [
                    'item' => $item,
                    'lecture' => $lecture,
                    'access' => $this->accessResolver->resolveState($student, $lecture),
                    'deadline' => $package->access_period_days
                        ? 'ينتهي الوصول بعد '.$package->access_period_days.' يوم من تاريخ التفعيل'
                        : null,
                    'related_exams' => $lecture->exams
                        ->map(fn (Exam $exam): array => $this->packageExamSummary($exam))
                        ->values(),
                    'files' => $lecture->assets
                        ->filter(fn ($asset): bool => filled($asset->url))
                        ->map(fn ($asset): array => ['asset' => $asset, 'lecture' => $lecture])
                        ->values(),
                ];
            })
            ->values();

        $lectureIds = $contentItems->pluck('lecture.id')->filter()->all();
        $questionItems = $contentItems
            ->flatMap(fn (array $row) => $row['related_exams']->map(function (array $examRow) use ($row): array {
                return [
                    ...$examRow,
                    'lecture' => $row['lecture'],
                ];
            }))
            ->values();
        $fileItems = $contentItems
            ->flatMap(fn (array $row) => $row['files'])
            ->values();
        $recommendations = Lecture::query()
            ->with(['product', 'lectureSection'])
            ->where('grade_id', $student->grade_id)
            ->when($student->track_id, fn ($query) => $query->where(function ($builder) use ($student): void {
                $builder->whereNull('track_id')->orWhere('track_id', $student->track_id);
            }))
            ->when($lectureIds !== [], fn ($query) => $query->whereNotIn('id', $lectureIds))
            ->where('is_active', true)
            ->orderByDesc('is_featured')
            ->orderByDesc('published_at')
            ->limit(4)
            ->get()
            ->map(fn (Lecture $lecture): array => [
                'lecture' => $lecture,
                'access' => $this->accessResolver->resolveState($student, $lecture),
            ]);
        $inCart = CartItem::query()
            ->whereHas('cart', fn ($query) => $query->where('student_id', $student->id))
            ->where('product_id', $package->product_id)
            ->exists();
        $isCampOffer = str_contains(
            mb_strtolower(($package->product?->name_ar ?? '').' '.($package->billing_cycle_label ?? '')),
            'معسكر'
        ) || str_contains((string) $package->product?->slug, 'camp');

        return view('student.catalog.packages.show', [
            'package' => $package,
            'eligibility' => $this->evaluatePackageEligibilityAction->execute($student, $package),
            'contentItems' => $contentItems,
            'questionItems' => $questionItems,
            'fileItems' => $fileItems,
            'recommendations' => $recommendations,
            'inCart' => $inCart,
            'isCampOffer' => $isCampOffer,
        ]);
    }

    /**
     * @return array{exam: Exam, max_attempts: int, remaining_attempts: int, cta: array{label: string, href: string}}
     */
    private function packageExamSummary(Exam $exam): array
    {
        $maxAttempts = max(1, (int) data_get($exam->metadata, 'max_attempts', 1));
        $usedAttempts = $exam->attempts->count();
        $currentAttempt = $exam->attempts->first(fn (ExamAttempt $attempt): bool => $attempt->status === ExamAttemptStatus::InProgress);
        $gradedAttempt = $exam->attempts->first(fn (ExamAttempt $attempt): bool => $attempt->status === ExamAttemptStatus::Graded);

        $cta = match (true) {
            $currentAttempt instanceof ExamAttempt => [
                'label' => 'استكمال المحاولة',
                'href' => route('student.exam-attempts.show', $currentAttempt),
            ],
            $gradedAttempt instanceof ExamAttempt => [
                'label' => 'عرض النتائج',
                'href' => route('student.exam-attempts.result', $gradedAttempt),
            ],
            default => [
                'label' => 'فتح الاختبار',
                'href' => route('student.lectures.exams.show', $exam),
            ],
        };

        return [
            'exam' => $exam,
            'max_attempts' => $maxAttempts,
            'remaining_attempts' => max(0, $maxAttempts - $usedAttempts),
            'cta' => $cta,
        ];
    }
}
