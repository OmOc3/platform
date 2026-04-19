<?php

namespace App\Modules\Identity\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Identity\Models\AuditLog;
use App\Modules\Identity\Queries\AuditLogsIndexQuery;
use App\Shared\Support\Exports\CsvExporter;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AuditLogController extends Controller
{
    public function __construct(private readonly AuditLogsIndexQuery $auditLogsIndexQuery)
    {
    }

    public function __invoke(Request $request): View|StreamedResponse
    {
        $this->authorize('viewAny', AuditLog::class);

        $query = $this->auditLogsIndexQuery->builder($request);

        if ($request->string('export')->toString() === 'csv') {
            return CsvExporter::download('audit-logs.csv', ['الحدث', 'الفاعل', 'الكيان', 'التاريخ'], $query->get()
                ->map(fn (AuditLog $auditLog): array => [
                    $auditLog->event,
                    (string) ($auditLog->actor_type ?? '-'),
                    (string) ($auditLog->auditable_type ?? '-'),
                    optional($auditLog->created_at)->format('Y-m-d H:i:s'),
                ])
                ->all());
        }

        return view('admin.identity.audit-logs.index', [
            'auditLogs' => $query->paginate(15)->withQueryString(),
        ]);
    }
}
