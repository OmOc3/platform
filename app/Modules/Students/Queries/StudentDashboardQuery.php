<?php

namespace App\Modules\Students\Queries;

use App\Modules\Commerce\Models\Entitlement;
use App\Modules\Commerce\Models\Product;
use App\Modules\Students\Models\Student;
use App\Shared\Enums\ProductKind;
use App\Shared\Enums\StudentStatus;

class StudentDashboardQuery
{
    /**
     * @return array<string, mixed>
     */
    public function dataFor(Student $student): array
    {
        $latestPackages = Product::query()
            ->with('package')
            ->where('kind', ProductKind::Package->value)
            ->where('is_active', true)
            ->latest('published_at')
            ->limit(3)
            ->get();

        $latestAccessibleContent = Entitlement::query()
            ->with('product')
            ->where('student_id', $student->id)
            ->latest('granted_at')
            ->limit(4)
            ->get();

        $notices = [];

        if ($student->status === StudentStatus::Pending) {
            $notices[] = [
                'tone' => 'warning',
                'title' => 'طلب التسجيل قيد المراجعة',
                'body' => 'يمكنك استخدام البوابة الأساسية الآن، وسيتم تفعيل مزايا الاشتراك والمحتوى بعد اعتماد الإدارة.',
            ];
        }

        return [
            'latestPackages' => $latestPackages,
            'latestAccessibleContent' => $latestAccessibleContent,
            'notices' => $notices,
            'stats' => [
                ['label' => 'المدفوعات الرقمية', 'value' => $student->entitlements()->count()],
                ['label' => 'طلبات الكتب', 'value' => $student->orders()->where('kind', 'book')->count()],
                ['label' => 'سجل الحضور', 'value' => $student->attendanceRecords()->count()],
            ],
        ];
    }
}
