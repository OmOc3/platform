<?php

namespace App\Modules\Academic\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Academic\Actions\Exams\SaveExamAction;
use App\Modules\Academic\Http\Requests\Admin\Exams\StoreExamRequest;
use App\Modules\Academic\Http\Requests\Admin\Exams\UpdateExamRequest;
use App\Modules\Academic\Models\Exam;
use App\Modules\Academic\Models\Grade;
use App\Modules\Academic\Models\Lecture;
use App\Modules\Academic\Models\Track;
use App\Modules\Academic\Queries\ExamsIndexQuery;
use App\Shared\Contracts\AuditLogger;
use App\Shared\Support\Exports\CsvExporter;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExamController extends Controller
{
    public function __construct(
        private readonly ExamsIndexQuery $examsIndexQuery,
        private readonly SaveExamAction $saveExamAction,
        private readonly AuditLogger $auditLogger,
    ) {
    }

    public function index(Request $request): View|StreamedResponse
    {
        $this->authorize('viewAny', Exam::class);

        $query = $this->examsIndexQuery->builder($request);

        if ($request->string('export')->toString() === 'csv') {
            return CsvExporter::download('exams.csv', ['الاختبار', 'المحاضرة', 'الصف', 'المدة', 'الحالة'], $query->get()
                ->map(fn (Exam $exam): array => [
                    $exam->title,
                    $exam->lecture?->title ?? '-',
                    $exam->grade?->name_ar ?? '-',
                    (string) ($exam->duration_minutes ?? 0),
                    $exam->is_active ? 'نشط' : 'متوقف',
                ])
                ->all());
        }

        return view('admin.academic.exams.index', [
            'exams' => $query->paginate(15)->withQueryString(),
            'grades' => Grade::query()->orderBy('sort_order')->get(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Exam::class);

        return view('admin.academic.exams.create', [
            'exam' => new Exam(['is_active' => true, 'is_featured' => false, 'is_free' => true, 'currency' => 'EGP']),
            ...$this->formData(),
        ]);
    }

    public function store(StoreExamRequest $request): RedirectResponse
    {
        $this->authorize('create', Exam::class);

        $this->saveExamAction->execute($request->validated(), auth('admin')->user());

        return redirect()
            ->route('admin.exams.index')
            ->with('status', 'تم إنشاء الاختبار.');
    }

    public function edit(Exam $exam): View
    {
        $this->authorize('update', $exam);

        return view('admin.academic.exams.edit', [
            'exam' => $exam->load(['examQuestions.question.choices']),
            ...$this->formData(),
        ]);
    }

    public function update(UpdateExamRequest $request, Exam $exam): RedirectResponse
    {
        $this->authorize('update', $exam);

        $this->saveExamAction->execute($request->validated(), auth('admin')->user(), $exam);

        return redirect()
            ->route('admin.exams.index')
            ->with('status', 'تم تحديث الاختبار.');
    }

    public function destroy(Exam $exam): RedirectResponse
    {
        $this->authorize('delete', $exam);

        $oldValues = $exam->toArray();
        $exam->delete();

        $this->auditLogger->log(
            event: 'academic.exam.deleted',
            actor: auth('admin')->user(),
            subject: $exam,
            oldValues: $oldValues,
        );

        return redirect()
            ->route('admin.exams.index')
            ->with('status', 'تم حذف الاختبار.');
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(): array
    {
        return [
            'lectures' => Lecture::query()->orderBy('title')->get(),
            'grades' => Grade::query()->orderBy('sort_order')->get(),
            'tracks' => Track::query()->orderBy('grade_id')->orderBy('sort_order')->get(),
        ];
    }
}
