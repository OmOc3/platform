<?php

namespace App\Modules\Academic\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Academic\Actions\LectureSections\SaveLectureSectionAction;
use App\Modules\Academic\Http\Requests\Admin\LectureSections\StoreLectureSectionRequest;
use App\Modules\Academic\Http\Requests\Admin\LectureSections\UpdateLectureSectionRequest;
use App\Modules\Academic\Models\CurriculumSection;
use App\Modules\Academic\Models\Grade;
use App\Modules\Academic\Models\LectureSection;
use App\Modules\Academic\Models\Track;
use App\Modules\Academic\Queries\LectureSectionsIndexQuery;
use App\Shared\Contracts\AuditLogger;
use App\Shared\Support\Exports\CsvExporter;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LectureSectionController extends Controller
{
    public function __construct(
        private readonly LectureSectionsIndexQuery $lectureSectionsIndexQuery,
        private readonly SaveLectureSectionAction $saveLectureSectionAction,
        private readonly AuditLogger $auditLogger,
    ) {
    }

    public function index(Request $request): View|StreamedResponse
    {
        $this->authorize('viewAny', LectureSection::class);

        $query = $this->lectureSectionsIndexQuery->builder($request);

        if ($request->string('export')->toString() === 'csv') {
            return CsvExporter::download('lecture-sections.csv', ['القسم', 'المنهج', 'الصف', 'الحالة'], $query->get()
                ->map(fn (LectureSection $section): array => [
                    $section->name_ar,
                    $section->curriculumSection?->name_ar ?? '-',
                    $section->grade?->name_ar ?? '-',
                    $section->is_active ? 'نشط' : 'متوقف',
                ])
                ->all());
        }

        return view('admin.academic.lecture-sections.index', [
            'sections' => $query->paginate(15)->withQueryString(),
            'grades' => Grade::query()->orderBy('sort_order')->get(),
            'curriculumSections' => CurriculumSection::query()->orderBy('sort_order')->get(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', LectureSection::class);

        return view('admin.academic.lecture-sections.create', [
            'section' => new LectureSection(['is_active' => true]),
            'grades' => Grade::query()->orderBy('sort_order')->get(),
            'tracks' => Track::query()->orderBy('grade_id')->orderBy('sort_order')->get(),
            'curriculumSections' => CurriculumSection::query()->orderBy('sort_order')->get(),
        ]);
    }

    public function store(StoreLectureSectionRequest $request): RedirectResponse
    {
        $this->authorize('create', LectureSection::class);

        $this->saveLectureSectionAction->execute($request->validated(), auth('admin')->user());

        return redirect()
            ->route('admin.lecture-sections.index')
            ->with('status', 'تم إنشاء قسم المحاضرات.');
    }

    public function edit(LectureSection $lectureSection): View
    {
        $this->authorize('update', $lectureSection);

        return view('admin.academic.lecture-sections.edit', [
            'section' => $lectureSection,
            'grades' => Grade::query()->orderBy('sort_order')->get(),
            'tracks' => Track::query()->orderBy('grade_id')->orderBy('sort_order')->get(),
            'curriculumSections' => CurriculumSection::query()->orderBy('sort_order')->get(),
        ]);
    }

    public function update(UpdateLectureSectionRequest $request, LectureSection $lectureSection): RedirectResponse
    {
        $this->authorize('update', $lectureSection);

        $this->saveLectureSectionAction->execute($request->validated(), auth('admin')->user(), $lectureSection);

        return redirect()
            ->route('admin.lecture-sections.index')
            ->with('status', 'تم تحديث قسم المحاضرات.');
    }

    public function destroy(LectureSection $lectureSection): RedirectResponse
    {
        $this->authorize('delete', $lectureSection);

        $oldValues = $lectureSection->toArray();
        $lectureSection->delete();

        $this->auditLogger->log(
            event: 'academic.lecture-section.deleted',
            actor: auth('admin')->user(),
            subject: $lectureSection,
            oldValues: $oldValues,
        );

        return redirect()
            ->route('admin.lecture-sections.index')
            ->with('status', 'تم حذف قسم المحاضرات.');
    }
}
