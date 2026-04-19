<?php

namespace Database\Seeders;

use App\Modules\Academic\Models\Grade;
use App\Modules\Academic\Models\Track;
use App\Modules\Centers\Models\AttendanceRecord;
use App\Modules\Centers\Models\AttendanceSession;
use App\Modules\Centers\Models\EducationalCenter;
use App\Modules\Centers\Models\EducationalGroup;
use App\Modules\Commerce\Models\Entitlement;
use App\Modules\Commerce\Models\Order;
use App\Modules\Commerce\Models\OrderItem;
use App\Modules\Commerce\Models\Product;
use App\Modules\Identity\Models\Admin;
use App\Modules\Students\Enums\StudentSourceType;
use App\Modules\Students\Models\Student;
use App\Modules\Students\Models\StudentStatusHistory;
use App\Modules\Support\Enums\ComplaintType;
use App\Modules\Support\Models\Complaint;
use App\Shared\Enums\AttendanceStatus;
use App\Shared\Enums\EntitlementSource;
use App\Shared\Enums\OrderKind;
use App\Shared\Enums\OrderStatus;
use App\Shared\Enums\StudentStatus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class StudentPortalSeeder extends Seeder
{
    public function run(): void
    {
        $owner = Admin::query()->first();
        $grade = Grade::query()->orderBy('sort_order')->first();
        $track = Track::query()->where('grade_id', $grade?->id)->orderBy('sort_order')->first();
        $center = EducationalCenter::query()->first();
        $group = EducationalGroup::query()->first();

        $pendingStudent = Student::query()->updateOrCreate(
            ['email' => 'pending.student@example.edu'],
            [
                'uuid' => (string) Str::uuid(),
                'student_number' => 'STU-100001',
                'name' => 'طالب قيد المراجعة',
                'phone' => '01012345670',
                'parent_phone' => '01012345671',
                'governorate' => 'القاهرة',
                'owner_admin_id' => $owner?->id,
                'grade_id' => $grade?->id,
                'track_id' => $track?->id,
                'source_type' => StudentSourceType::Online,
                'is_azhar' => false,
                'notes' => 'تسجيل ذاتي جديد يحتاج مراجعة.',
                'status' => StudentStatus::Pending,
                'language' => 'ar',
                'password' => 'password',
            ],
        );

        StudentStatusHistory::query()->firstOrCreate(
            [
                'student_id' => $pendingStudent->id,
                'new_status' => StudentStatus::Pending,
            ],
            [
                'previous_status' => null,
                'reason' => 'تسجيل ذاتي',
                'actor_type' => null,
                'actor_id' => null,
                'created_at' => now()->subDays(4),
            ],
        );

        $student = Student::query()->updateOrCreate(
            ['email' => 'student@example.edu'],
            [
                'uuid' => (string) Str::uuid(),
                'student_number' => 'STU-100002',
                'name' => 'طالب مشترك',
                'phone' => '01012345672',
                'parent_phone' => '01012345673',
                'governorate' => 'الجيزة',
                'owner_admin_id' => $owner?->id,
                'grade_id' => $grade?->id,
                'track_id' => $track?->id,
                'center_id' => $center?->id,
                'group_id' => $group?->id,
                'source_type' => StudentSourceType::Hybrid,
                'is_azhar' => false,
                'notes' => 'طالب نشط للعرض التجريبي.',
                'status' => StudentStatus::Subscribed,
                'language' => 'ar',
                'password' => 'password',
            ],
        );

        StudentStatusHistory::query()->firstOrCreate(
            [
                'student_id' => $student->id,
                'new_status' => StudentStatus::Subscribed,
            ],
            [
                'previous_status' => StudentStatus::Pending,
                'reason' => 'تمت الموافقة إداريًا',
                'actor_type' => $owner?->getMorphClass(),
                'actor_id' => $owner?->getKey(),
                'created_at' => now()->subDays(15),
            ],
        );

        $packageProduct = Product::query()->where('kind', 'package')->orderBy('published_at')->first();
        $bookProduct = Product::query()->where('kind', 'book')->orderBy('published_at')->first();

        $digitalOrder = Order::query()->firstOrCreate(
            ['uuid' => 'digital-order-demo-100002'],
            [
                'student_id' => $student->id,
                'kind' => OrderKind::Digital,
                'status' => OrderStatus::Paid,
                'subtotal_amount' => 399,
                'total_amount' => 399,
                'currency' => 'EGP',
                'placed_at' => now()->subDays(12),
            ],
        );

        $digitalOrderItem = OrderItem::query()->firstOrCreate(
            [
                'order_id' => $digitalOrder->id,
                'product_name_snapshot' => $packageProduct?->name_ar ?? 'باقة الفيزياء الشهرية',
            ],
            [
                'product_id' => $packageProduct?->id,
                'product_kind' => $packageProduct?->kind?->value ?? 'package',
                'quantity' => 1,
                'unit_price_amount' => 399,
                'total_price_amount' => 399,
                'meta' => null,
            ],
        );

        Entitlement::query()->firstOrCreate(
            [
                'student_id' => $student->id,
                'product_id' => $packageProduct?->id,
                'source' => EntitlementSource::DirectPurchase,
            ],
            [
                'order_item_id' => $digitalOrderItem->id,
                'status' => 'active',
                'item_name_snapshot' => $packageProduct?->name_ar ?? 'باقة الفيزياء الشهرية',
                'price_amount' => 399,
                'currency' => 'EGP',
                'granted_by_admin_id' => null,
                'granted_at' => now()->subDays(12),
                'starts_at' => now()->subDays(12),
                'ends_at' => now()->addDays(18),
                'meta' => ['note' => 'اشتراك مدفوع مباشر'],
            ],
        );

        Entitlement::query()->firstOrCreate(
            [
                'student_id' => $student->id,
                'product_id' => $packageProduct?->id,
                'source' => EntitlementSource::AdminGrant,
            ],
            [
                'order_item_id' => null,
                'status' => 'active',
                'item_name_snapshot' => 'منحة مراجعة سريعة',
                'price_amount' => 0,
                'currency' => 'EGP',
                'granted_by_admin_id' => $owner?->id,
                'granted_at' => now()->subDays(2),
                'starts_at' => now()->subDays(2),
                'ends_at' => now()->addDays(28),
                'meta' => ['note' => 'منحة إدارية'],
            ],
        );

        $bookOrder = Order::query()->firstOrCreate(
            ['uuid' => 'book-order-demo-100002'],
            [
                'student_id' => $student->id,
                'kind' => OrderKind::Book,
                'status' => OrderStatus::Fulfilled,
                'subtotal_amount' => 180,
                'total_amount' => 180,
                'currency' => 'EGP',
                'placed_at' => now()->subDays(7),
            ],
        );

        OrderItem::query()->firstOrCreate(
            [
                'order_id' => $bookOrder->id,
                'product_name_snapshot' => $bookProduct?->name_ar ?? 'كتاب الحلول الذكية',
            ],
            [
                'product_id' => $bookProduct?->id,
                'product_kind' => $bookProduct?->kind?->value ?? 'book',
                'quantity' => 1,
                'unit_price_amount' => 180,
                'total_price_amount' => 180,
                'meta' => null,
            ],
        );

        $sessions = AttendanceSession::query()->orderBy('starts_at')->get();

        foreach ($sessions as $index => $session) {
            AttendanceRecord::query()->updateOrCreate(
                [
                    'attendance_session_id' => $session->id,
                    'student_id' => $student->id,
                ],
                [
                    'attendance_status' => $index === 0 ? AttendanceStatus::Present : AttendanceStatus::Late,
                    'exam_status_label' => $session->session_type === 'exam' ? 'تم الاختبار' : 'لم يختبر بعد',
                    'score' => $session->session_type === 'exam' ? 17 : null,
                    'max_score' => $session->session_type === 'exam' ? 20 : null,
                    'notes' => null,
                    'recorded_at' => $session->starts_at,
                ],
            );
        }

        foreach ([
            [ComplaintType::Complaint, 'أحتاج توضيحًا بخصوص موعد تفعيل الباقة.', now()->subDays(5)],
            [ComplaintType::Suggestion, 'أتمنى إضافة قسم مختصر للقوانين المهمة في الصفحة الرئيسية.', now()->subDay()],
        ] as [$type, $content, $createdAt]) {
            Complaint::query()->firstOrCreate(
                [
                    'student_id' => $student->id,
                    'type' => $type,
                    'content' => $content,
                ],
                [
                    'status' => 'open',
                    'admin_notes' => null,
                    'resolved_at' => null,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ],
            );
        }
    }
}
