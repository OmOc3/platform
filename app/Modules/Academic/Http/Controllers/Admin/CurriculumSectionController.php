<?php

namespace App\Modules\Academic\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Academic\Actions\CurriculumSections\SaveCurriculumSectionAction;
use App\Modules\Academic\Http\Requests\Admin\CurriculumSections\StoreCurriculumSectionRequest;
use App\Modules\Academic\Http\Requests\Admin\CurriculumSections\UpdateCurriculumSectionRequest;
use App\Modules\Academic\Models\CurriculumSection;
use App\Modules\Academic\Models\Grade;
use App\Modules\Academic\Models\Track;
use App\Modules\Academic\Queries\CurriculumSectionsIndexQuery;
use App\Shared\Contracts\AuditLogger;
use App\Shared\Support\Exports\CsvExporter;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CurriculumSectionController extends Controller
{
    public function __construct(
        private readonly CurriculumSectionsIndexQuery $curriculumSectionsIndexQuery,
        private readonly SaveCurriculumSectionAction $saveCurriculumSectionAction,
        private readonly AuditLogger $auditLogger,
    ) {
    }

    public function index(Request $request): View|StreamedResponse
    {
        $this->authorize('viewAny', CurriculumSection::class);

        $query = $this->curriculumSectionsIndexQuery->builder($request);

        if ($request->string('export')->toString() === 'csv') {
            return CsvExporter::download('curriculum-sections.csv', ['القسم', 'الصف', 'المسار', 'الحالة'], $query->get()
                ->map(fn (CurriculumSection $section): array => [
                    $section->name_ar,
                    $section->grade?->name_ar ?? '-',
                    $section->track?->name_ar ?? '-',
                    $section->is_active ? 'نشط' : 'متوقف',
                ])
                ->all());
        }

        return view('admin.academic.curriculum-sections.index', [
            'sections' => $query->paginate(15)->withQueryString(),
            'grades' => Grade::query()->orderBy('sort_order')->get(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', CurriculumSection::class);

        return view('admin.academic.curriculum-sections.create', [
            'section' => new CurriculumSection(['is_active' => true]),
            'grades' => Grade::query()->orderBy('sort_order')->get(),
            'tracks' => Track::query()->orderBy('grade_id')->orderBy('sort_order')->get(),
        ]);
    }

    public function store(StoreCurriculumSectionRequest $request): RedirectResponse
    {
        $this->authorize('create', CurriculumSection::class);

        $this->saveCurriculumSectionAction->execute($request->validated(), auth('admin')->user());

        return redirect()
            ->route('admin.curriculum-sections.index')
            ->with('status', 'تم إنشاء قسم المنهج.');
    }

    public function edit(CurriculumSection $curriculumSection): View
    {
        $this->authorize('update', $curriculumSection);

        return view('admin.academic.curriculum-sections.edit', [
            'section' => $curriculumSection,
            'grades' => Grade::query()->orderBy('sort_order')->get(),
            'tracks' => Track::query()->orderBy('grade_id')->orderBy('sort_order')->get(),
        ]);
    }

    public function update(UpdateCurriculumSectionRequest $request, CurriculumSection $curriculumSection): RedirectResponse
    {
        $this->authorize('update', $curriculumSection);

        $this->saveCurriculumSectionAction->execute($request->validated(), auth('admin')->user(), $curriculumSection);

        return redirect()
            ->route('admin.curriculum-sections.index')
            ->with('status', 'تم تحديث قسم المنهج.');
    }

    public function destroy(CurriculumSection $curriculumSection): RedirectResponse
    {
        $this->authorize('delete', $curriculumSection);

        $oldValues = $curriculumSection->toArray();
        $curriculumSection->delete();

        $this->auditLogger->log(
            event: 'academic.curriculum-section.deleted',
            actor: auth('admin')->user(),
            subject: $curriculumSection,
            oldValues: $oldValues,
        );

        return redirect()
            ->route('admin.curriculum-sections.index')
            ->with('status', 'تم حذف قسم المنهج.');
    }
}
