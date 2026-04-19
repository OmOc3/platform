<?php

namespace App\Modules\Identity\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Academic\Models\Grade;
use App\Modules\Academic\Models\Track;
use App\Modules\Identity\Models\Admin;
use App\Modules\Identity\Models\AuditLog;
use App\Modules\Identity\Models\Setting;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        abort_unless(auth('admin')->user()?->can('dashboard.view'), 403);

        return view('admin.dashboard', [
            'stats' => [
                ['label' => 'المشرفون', 'value' => Admin::query()->count()],
                ['label' => 'الإعدادات', 'value' => Setting::query()->count()],
                ['label' => 'الصفوف', 'value' => Grade::query()->count()],
                ['label' => 'المسارات', 'value' => Track::query()->count()],
            ],
            'latestAuditLogs' => AuditLog::query()
                ->latest('created_at')
                ->limit(8)
                ->get(),
        ]);
    }
}
