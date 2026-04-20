<?php

namespace App\Modules\Identity\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Academic\Models\Grade;
use App\Modules\Academic\Models\Track;
use App\Modules\Centers\Models\AttendanceRecord;
use App\Modules\Centers\Models\EducationalCenter;
use App\Modules\Commerce\Models\Order;
use App\Modules\Identity\Models\Admin;
use App\Modules\Identity\Models\AuditLog;
use App\Modules\Identity\Models\Setting;
use App\Modules\Students\Models\Student;
use App\Modules\Support\Models\Complaint;
use App\Shared\Enums\ComplaintStatus;
use App\Shared\Enums\OrderStatus;
use App\Shared\Enums\StudentStatus;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        abort_unless(auth('admin')->user()?->can('dashboard.view'), 403);

        return view('admin.dashboard', [
            'stats' => [
                ['label' => 'الطلاب النشطون', 'value' => Student::query()->where('status', StudentStatus::Subscribed->value)->count()],
                ['label' => 'طلبات تحتاج متابعة', 'value' => Order::query()->whereIn('status', [OrderStatus::PendingPayment->value, OrderStatus::Paid->value])->count()],
                ['label' => 'شكاوى مفتوحة', 'value' => Complaint::query()->whereIn('status', [ComplaintStatus::Open->value, ComplaintStatus::UnderReview->value])->count()],
                ['label' => 'سناتر فعالة', 'value' => EducationalCenter::query()->where('is_active', true)->count()],
            ],
            'secondaryStats' => [
                ['label' => 'المشرفون', 'value' => Admin::query()->count()],
                ['label' => 'الإعدادات', 'value' => Setting::query()->count()],
                ['label' => 'الصفوف', 'value' => Grade::query()->count()],
                ['label' => 'المسارات', 'value' => Track::query()->count()],
                ['label' => 'سجلات الحضور', 'value' => AttendanceRecord::query()->count()],
            ],
            'latestAuditLogs' => AuditLog::query()
                ->latest('created_at')
                ->limit(8)
                ->get(),
            'attentionItems' => [
                'registrations' => Student::query()
                    ->with(['grade', 'track'])
                    ->where('status', StudentStatus::Pending->value)
                    ->latest('created_at')
                    ->limit(5)
                    ->get(),
                'complaints' => Complaint::query()
                    ->with('student')
                    ->whereIn('status', [ComplaintStatus::Open->value, ComplaintStatus::UnderReview->value])
                    ->latest('created_at')
                    ->limit(5)
                    ->get(),
            ],
        ]);
    }
}
