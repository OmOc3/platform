<?php

namespace App\Modules\Centers\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Centers\Models\AttendanceSession;
use App\Modules\Centers\Models\EducationalCenter;
use App\Modules\Centers\Queries\AdminCentersIndexQuery;
use App\Modules\Students\Models\Student;
use App\Shared\Enums\AttendanceStatus;
use App\Shared\Support\Exports\CsvExporter;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CenterController extends Controller
{
    public function __construct(private readonly AdminCentersIndexQuery $adminCentersIndexQuery)
    {
    }

    public function index(Request $request): View|StreamedResponse
    {
        abort_unless(auth('admin')->user()?->can('centers.view'), 403);

        $query = $this->adminCentersIndexQuery->builder($request);

        if ($request->string('export')->toString() === 'csv') {
            return CsvExporter::download('centers.csv', ['السنتر', 'المدينة', 'المجموعات', 'الطلاب', 'الحالة'], $query->get()
                ->map(fn (EducationalCenter $center): array => [
                    $center->name_ar,
                    $center->city ?? '-',
                    (string) $center->groups_count,
                    (string) $center->students_count,
                    $center->is_active ? 'نشط' : 'متوقف',
                ])
                ->all());
        }

        return view('admin.centers.index', [
            'centers' => $query->paginate(12)->withQueryString(),
            'overview' => [
                'total' => EducationalCenter::query()->count(),
                'active' => EducationalCenter::query()->where('is_active', true)->count(),
                'groups' => \App\Modules\Centers\Models\EducationalGroup::query()->count(),
                'students' => Student::query()->whereNotNull('center_id')->count(),
            ],
        ]);
    }

    public function show(EducationalCenter $center): View
    {
        abort_unless(auth('admin')->user()?->can('centers.view'), 403);

        $center->loadCount('students')
            ->load([
                'groups' => fn ($query) => $query
                    ->withCount(['students', 'sessions'])
                    ->orderByDesc('is_active')
                    ->orderBy('name_ar'),
            ]);

        $recentSessions = AttendanceSession::query()
            ->with('group')
            ->withCount([
                'records',
                'records as present_records_count' => fn ($query) => $query->where('attendance_status', AttendanceStatus::Present->value),
                'records as late_records_count' => fn ($query) => $query->where('attendance_status', AttendanceStatus::Late->value),
                'records as absent_records_count' => fn ($query) => $query->where('attendance_status', AttendanceStatus::Absent->value),
            ])
            ->whereHas('group', fn ($query) => $query->where('center_id', $center->id))
            ->latest('starts_at')
            ->limit(8)
            ->get();

        $recentStudents = Student::query()
            ->with(['group', 'ownerAdmin'])
            ->where('center_id', $center->id)
            ->latest('updated_at')
            ->limit(8)
            ->get();

        return view('admin.centers.show', [
            'center' => $center,
            'recentSessions' => $recentSessions,
            'recentStudents' => $recentStudents,
        ]);
    }
}
