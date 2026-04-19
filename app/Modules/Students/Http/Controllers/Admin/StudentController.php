<?php

namespace App\Modules\Students\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Academic\Models\Grade;
use App\Modules\Academic\Models\Track;
use App\Modules\Centers\Models\EducationalCenter;
use App\Modules\Centers\Models\EducationalGroup;
use App\Modules\Identity\Models\Admin;
use App\Modules\Students\Actions\Admin\UpdateStudentByAdminAction;
use App\Modules\Students\Enums\StudentSourceType;
use App\Modules\Students\Http\Requests\Admin\UpdateStudentRequest;
use App\Modules\Students\Models\Student;
use App\Modules\Students\Queries\StudentsIndexQuery;
use App\Shared\Enums\StudentStatus;
use App\Shared\Support\Exports\CsvExporter;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StudentController extends Controller
{
    public function __construct(
        private readonly StudentsIndexQuery $studentsIndexQuery,
        private readonly UpdateStudentByAdminAction $updateStudentByAdminAction,
    ) {
    }

    public function index(Request $request): View|StreamedResponse
    {
        $this->authorize('viewAny', Student::class);

        $query = $this->studentsIndexQuery->builder($request);

        if ($request->string('export')->toString() === 'csv') {
            return CsvExporter::download('students.csv', ['الطالب', 'الرقم', 'الحالة', 'المصدر', 'الصف', 'المسار'], $query->get()
                ->map(fn (Student $student): array => [
                    $student->name,
                    $student->student_number,
                    $student->status->value,
                    $student->source_type?->value,
                    $student->grade?->name_ar ?? '-',
                    $student->track?->name_ar ?? '-',
                ])
                ->all());
        }

        return view('admin.students.index', [
            'students' => $query->paginate(15)->withQueryString(),
            'owners' => Admin::query()->orderBy('name')->get(),
            'grades' => Grade::query()->orderBy('sort_order')->get(),
            'tracks' => Track::query()->orderBy('grade_id')->orderBy('sort_order')->get(),
            'statuses' => StudentStatus::cases(),
            'sourceTypes' => StudentSourceType::cases(),
        ]);
    }

    public function edit(Student $student): View
    {
        $this->authorize('update', $student);

        return view('admin.students.edit', [
            'student' => $student->load(['ownerAdmin', 'grade', 'track', 'center', 'group', 'statusHistories.actor']),
            'owners' => Admin::query()->orderBy('name')->get(),
            'grades' => Grade::query()->orderBy('sort_order')->get(),
            'tracks' => Track::query()->orderBy('grade_id')->orderBy('sort_order')->get(),
            'centers' => EducationalCenter::query()->orderBy('name_ar')->get(),
            'groups' => EducationalGroup::query()->orderBy('name_ar')->get(),
            'statuses' => StudentStatus::cases(),
            'sourceTypes' => StudentSourceType::cases(),
        ]);
    }

    public function update(UpdateStudentRequest $request, Student $student): RedirectResponse
    {
        $this->authorize('update', $student);

        $this->updateStudentByAdminAction->execute($student, $request->validated(), auth('admin')->user());

        return redirect()
            ->route('admin.students.edit', $student)
            ->with('status', 'تم تحديث بيانات الطالب.');
    }
}
