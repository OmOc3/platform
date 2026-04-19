<?php

namespace App\Modules\Academic\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Academic\Actions\Grades\CreateGradeAction;
use App\Modules\Academic\Actions\Grades\UpdateGradeAction;
use App\Modules\Academic\Http\Requests\Admin\Grades\StoreGradeRequest;
use App\Modules\Academic\Http\Requests\Admin\Grades\UpdateGradeRequest;
use App\Modules\Academic\Models\Grade;
use App\Modules\Academic\Queries\GradesIndexQuery;
use App\Shared\Contracts\AuditLogger;
use App\Shared\Support\Exports\CsvExporter;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class GradeController extends Controller
{
    public function __construct(
        private readonly GradesIndexQuery $gradesIndexQuery,
        private readonly CreateGradeAction $createGradeAction,
        private readonly UpdateGradeAction $updateGradeAction,
        private readonly AuditLogger $auditLogger,
    ) {
    }

    public function index(Request $request): View|StreamedResponse
    {
        $this->authorize('viewAny', Grade::class);

        $query = $this->gradesIndexQuery->builder($request);

        if ($request->string('export')->toString() === 'csv') {
            return CsvExporter::download('grades.csv', ['الاسم', 'الكود', 'الترتيب', 'الحالة'], $query->get()
                ->map(fn (Grade $grade): array => [
                    $grade->name_ar,
                    $grade->code,
                    $grade->sort_order,
                    $grade->is_active ? 'نشط' : 'متوقف',
                ])
                ->all());
        }

        return view('admin.academic.grades.index', [
            'grades' => $query->paginate(15)->withQueryString(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Grade::class);

        return view('admin.academic.grades.create', [
            'grade' => new Grade(['is_active' => true]),
        ]);
    }

    public function store(StoreGradeRequest $request): RedirectResponse
    {
        $this->authorize('create', Grade::class);

        $this->createGradeAction->execute($request->validated(), auth('admin')->user());

        return redirect()
            ->route('admin.grades.index')
            ->with('status', 'تم إنشاء الصف الدراسي.');
    }

    public function edit(Grade $grade): View
    {
        $this->authorize('update', $grade);

        return view('admin.academic.grades.edit', [
            'grade' => $grade,
        ]);
    }

    public function update(UpdateGradeRequest $request, Grade $grade): RedirectResponse
    {
        $this->authorize('update', $grade);

        $this->updateGradeAction->execute($grade, $request->validated(), auth('admin')->user());

        return redirect()
            ->route('admin.grades.index')
            ->with('status', 'تم تحديث الصف الدراسي.');
    }

    public function destroy(Grade $grade): RedirectResponse
    {
        $this->authorize('delete', $grade);

        $oldValues = $grade->toArray();
        $grade->delete();

        $this->auditLogger->log(
            event: 'academic.grade.deleted',
            actor: auth('admin')->user(),
            subject: $grade,
            oldValues: $oldValues,
        );

        return redirect()
            ->route('admin.grades.index')
            ->with('status', 'تم حذف الصف الدراسي.');
    }
}
