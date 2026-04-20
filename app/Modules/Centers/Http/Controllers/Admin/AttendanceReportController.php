<?php

namespace App\Modules\Centers\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Centers\Models\AttendanceSession;
use App\Modules\Centers\Models\EducationalCenter;
use App\Modules\Centers\Models\EducationalGroup;
use App\Modules\Centers\Queries\AdminAttendanceSessionsQuery;
use App\Shared\Support\Exports\CsvExporter;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttendanceReportController extends Controller
{
    public function __construct(private readonly AdminAttendanceSessionsQuery $adminAttendanceSessionsQuery)
    {
    }

    public function index(Request $request): View|StreamedResponse
    {
        abort_unless(auth('admin')->user()?->can('attendance.view'), 403);

        $query = $this->adminAttendanceSessionsQuery->builder($request);

        if ($request->string('export')->toString() === 'csv') {
            return CsvExporter::download('attendance-report.csv', ['الجلسة', 'النوع', 'السنتر', 'المجموعة', 'الحضور', 'متأخر', 'غياب', 'التاريخ'], $query->get()
                ->map(fn (AttendanceSession $session): array => [
                    $session->title,
                    $session->session_type,
                    $session->group?->center?->name_ar ?? '-',
                    $session->group?->name_ar ?? '-',
                    (string) $session->present_records_count,
                    (string) $session->late_records_count,
                    (string) $session->absent_records_count,
                    (string) optional($session->starts_at)->format('Y-m-d H:i'),
                ])
                ->all());
        }

        return view('admin.centers.attendance.index', [
            'sessions' => $query->paginate(15)->withQueryString(),
            'centers' => EducationalCenter::query()->orderBy('name_ar')->get(),
            'groups' => EducationalGroup::query()
                ->when($request->filled('center_id'), fn ($query) => $query->where('center_id', $request->integer('center_id')))
                ->orderBy('name_ar')
                ->get(),
            'overview' => [
                'total' => AttendanceSession::query()->count(),
                'lectures' => AttendanceSession::query()->where('session_type', 'lecture')->count(),
                'exams' => AttendanceSession::query()->where('session_type', 'exam')->count(),
                'records' => \App\Modules\Centers\Models\AttendanceRecord::query()->count(),
            ],
        ]);
    }
}
