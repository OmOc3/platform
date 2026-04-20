<?php

namespace App\Modules\Support\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Support\Enums\ComplaintType;
use App\Modules\Support\Http\Requests\Admin\UpdateComplaintRequest;
use App\Modules\Support\Models\Complaint;
use App\Modules\Support\Queries\AdminComplaintsIndexQuery;
use App\Shared\Contracts\AuditLogger;
use App\Shared\Enums\ComplaintStatus;
use App\Shared\Support\Exports\CsvExporter;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ComplaintController extends Controller
{
    public function __construct(
        private readonly AdminComplaintsIndexQuery $adminComplaintsIndexQuery,
        private readonly AuditLogger $auditLogger,
    ) {
    }

    public function index(Request $request): View|StreamedResponse
    {
        $this->authorize('viewAny', Complaint::class);

        $query = $this->adminComplaintsIndexQuery->builder($request);

        if ($request->string('export')->toString() === 'csv') {
            return CsvExporter::download('complaints.csv', ['الطالب', 'النوع', 'الحالة', 'التاريخ', 'المحتوى'], $query->get()
                ->map(fn (Complaint $complaint): array => [
                    $complaint->student?->name ?? '-',
                    $complaint->type->label(),
                    $complaint->status->label(),
                    (string) optional($complaint->created_at)->format('Y-m-d H:i'),
                    str($complaint->content)->limit(120)->toString(),
                ])
                ->all());
        }

        return view('admin.support.complaints.index', [
            'complaints' => $query->paginate(15)->withQueryString(),
            'statuses' => ComplaintStatus::cases(),
            'types' => ComplaintType::cases(),
            'overview' => [
                'total' => Complaint::query()->count(),
                'open' => Complaint::query()->whereIn('status', [ComplaintStatus::Open->value, ComplaintStatus::UnderReview->value])->count(),
                'resolved' => Complaint::query()->where('status', ComplaintStatus::Resolved->value)->count(),
                'suggestions' => Complaint::query()->where('type', ComplaintType::Suggestion->value)->count(),
            ],
        ]);
    }

    public function show(Complaint $complaint): View
    {
        $this->authorize('view', $complaint);

        return view('admin.support.complaints.show', [
            'complaint' => $complaint->load(['student.ownerAdmin', 'student.center', 'student.group']),
            'statuses' => ComplaintStatus::cases(),
        ]);
    }

    public function update(UpdateComplaintRequest $request, Complaint $complaint): RedirectResponse
    {
        $this->authorize('update', $complaint);

        $oldValues = $complaint->toArray();
        $status = ComplaintStatus::from($request->validated('status'));

        $complaint->fill([
            'status' => $status,
            'admin_notes' => $request->validated('admin_notes'),
            'resolved_at' => $status === ComplaintStatus::Resolved
                ? ($complaint->resolved_at ?? now())
                : ($status === ComplaintStatus::Closed ? $complaint->resolved_at : null),
        ]);
        $complaint->save();

        $this->auditLogger->log(
            event: 'support.complaint.updated',
            actor: auth('admin')->user(),
            subject: $complaint,
            oldValues: $oldValues,
            newValues: $complaint->fresh()->toArray(),
        );

        return redirect()
            ->route('admin.complaints.show', $complaint)
            ->with('status', 'تم تحديث حالة الشكوى / الاقتراح.');
    }
}
