<?php

namespace App\Modules\Academic\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Academic\Actions\Lectures\SaveLectureAction;
use App\Modules\Academic\Http\Requests\Admin\Lectures\StoreLectureRequest;
use App\Modules\Academic\Http\Requests\Admin\Lectures\UpdateLectureRequest;
use App\Modules\Academic\Models\CurriculumSection;
use App\Modules\Academic\Models\Grade;
use App\Modules\Academic\Models\Lecture;
use App\Modules\Academic\Models\LectureSection;
use App\Modules\Academic\Models\Track;
use App\Modules\Academic\Queries\LecturesIndexQuery;
use App\Shared\Contracts\AuditLogger;
use App\Shared\Enums\ContentKind;
use App\Shared\Support\Exports\CsvExporter;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LectureController extends Controller
{
    public function __construct(
        private readonly LecturesIndexQuery $lecturesIndexQuery,
        private readonly SaveLectureAction $saveLectureAction,
        private readonly AuditLogger $auditLogger,
    ) {
    }

    public function index(Request $request): View|StreamedResponse
    {
        $this->authorize('viewAny', Lecture::class);

        $query = $this->lecturesIndexQuery->builder($request);

        if ($request->string('export')->toString() === 'csv') {
            return CsvExporter::download('lectures.csv', ['العنوان', 'النوع', 'الصف', 'السعر', 'الحالة'], $query->get()
                ->map(fn (Lecture $lecture): array => [
                    $lecture->title,
                    $lecture->type->value,
                    $lecture->grade?->name_ar ?? '-',
                    (string) $lecture->price_amount,
                    $lecture->is_active ? 'نشط' : 'متوقف',
                ])
                ->all());
        }

        return view('admin.academic.lectures.index', [
            'lectures' => $query->paginate(15)->withQueryString(),
            'grades' => Grade::query()->orderBy('sort_order')->get(),
            'types' => [ContentKind::Lecture, ContentKind::Review],
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Lecture::class);

        return view('admin.academic.lectures.create', [
            'lecture' => new Lecture(['is_active' => true, 'is_featured' => false, 'is_free' => false, 'currency' => 'EGP', 'type' => ContentKind::Lecture]),
            ...$this->formData(),
        ]);
    }

    public function store(StoreLectureRequest $request): RedirectResponse
    {
        $this->authorize('create', Lecture::class);

        $this->saveLectureAction->execute($request->validated(), auth('admin')->user());

        return redirect()
            ->route('admin.lectures.index')
            ->with('status', 'تم إنشاء المحتوى الأكاديمي.');
    }

    public function edit(Lecture $lecture): View
    {
        $this->authorize('update', $lecture);

        return view('admin.academic.lectures.edit', [
            'lecture' => $lecture,
            ...$this->formData(),
        ]);
    }

    public function update(UpdateLectureRequest $request, Lecture $lecture): RedirectResponse
    {
        $this->authorize('update', $lecture);

        $this->saveLectureAction->execute($request->validated(), auth('admin')->user(), $lecture);

        return redirect()
            ->route('admin.lectures.index')
            ->with('status', 'تم تحديث المحتوى الأكاديمي.');
    }

    public function destroy(Lecture $lecture): RedirectResponse
    {
        $this->authorize('delete', $lecture);

        $oldValues = $lecture->toArray();
        $lecture->delete();

        $this->auditLogger->log(
            event: 'academic.lecture.deleted',
            actor: auth('admin')->user(),
            subject: $lecture,
            oldValues: $oldValues,
        );

        return redirect()
            ->route('admin.lectures.index')
            ->with('status', 'تم حذف المحتوى الأكاديمي.');
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(): array
    {
        return [
            'grades' => Grade::query()->orderBy('sort_order')->get(),
            'tracks' => Track::query()->orderBy('grade_id')->orderBy('sort_order')->get(),
            'curriculumSections' => CurriculumSection::query()->orderBy('sort_order')->get(),
            'lectureSections' => LectureSection::query()->orderBy('sort_order')->get(),
            'types' => [ContentKind::Lecture, ContentKind::Review],
        ];
    }
}
