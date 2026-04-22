<?php

namespace Database\Seeders;

use App\Modules\Academic\Models\Lecture;
use App\Modules\Centers\Models\AttendanceRecord;
use App\Modules\Centers\Models\AttendanceSession;
use App\Modules\Centers\Models\EducationalCenter;
use App\Modules\Centers\Models\EducationalGroup;
use App\Modules\Commerce\Actions\Payments\HandlePaymentWebhookAction;
use App\Modules\Commerce\Actions\Payments\StartOrderPaymentAction;
use App\Modules\Commerce\Actions\Shipments\UpdateShipmentStatusAction;
use App\Modules\Commerce\Models\Cart;
use App\Modules\Commerce\Models\CartItem;
use App\Modules\Commerce\Models\Entitlement;
use App\Modules\Commerce\Models\Order;
use App\Modules\Commerce\Models\OrderItem;
use App\Modules\Commerce\Models\Product;
use App\Modules\Identity\Models\Admin;
use App\Modules\Students\Enums\StudentSourceType;
use App\Modules\Students\Models\MistakeItem;
use App\Modules\Students\Models\Student;
use App\Modules\Students\Models\StudentStatusHistory;
use App\Modules\Support\Enums\ComplaintType;
use App\Modules\Support\Enums\ForumThreadStatus;
use App\Modules\Support\Enums\ForumVisibility;
use App\Modules\Support\Models\Complaint;
use App\Modules\Support\Models\ForumThread;
use App\Modules\Support\Models\SupportTeam;
use App\Modules\Support\Models\SupportTicket;
use App\Modules\Support\Models\SupportTicketType;
use App\Shared\Contracts\AttendanceRecorder;
use App\Shared\Contracts\EntitlementGrantor;
use App\Shared\Contracts\LectureProgressService;
use App\Shared\Enums\AttendanceStatus;
use App\Shared\Enums\EntitlementSource;
use App\Shared\Enums\OrderKind;
use App\Shared\Enums\OrderStatus;
use App\Shared\Enums\PaymentStatus;
use App\Shared\Enums\ShipmentStatus;
use App\Shared\Enums\StudentStatus;
use App\Shared\Enums\TicketStatus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class StudentPortalSeeder extends Seeder
{
    public function run(): void
    {
        $owner = Admin::query()->first();
        $supportAgent = Admin::query()->firstWhere('email', 'support.agent@example.edu') ?? $owner;
        $grade = \App\Modules\Academic\Models\Grade::query()->where('code', 'grade-1-secondary')->first();
        $track = \App\Modules\Academic\Models\Track::query()->where('code', 'foundation-track')->first();
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

        $packageProduct = Product::query()->where('slug', 'monthly-physics-package')->first();
        $bookProduct = Product::query()->where('slug', 'smart-solutions-book')->first();
        $lectureProduct = Product::query()->where('slug', 'newton-laws-core')->first();
        $problemSolvingProduct = Product::query()->where('slug', 'accelerated-motion-problem-solving')->first();
        $reviewProduct = Product::query()->where('slug', 'electricity-review-essentials')->first();
        $freeLecture = Lecture::query()->where('slug', 'foundation-kinematics-free')->with('checkpoints')->first();
        $lecture = Lecture::query()->where('slug', 'newton-laws-core')->first();
        $reviewLecture = Lecture::query()->where('slug', 'electricity-review-essentials')->first();
        /** @var EntitlementGrantor $entitlementGrantor */
        $entitlementGrantor = app(EntitlementGrantor::class);
        /** @var AttendanceRecorder $attendanceRecorder */
        $attendanceRecorder = app(AttendanceRecorder::class);
        /** @var LectureProgressService $lectureProgressService */
        $lectureProgressService = app(LectureProgressService::class);
        /** @var StartOrderPaymentAction $startOrderPaymentAction */
        $startOrderPaymentAction = app(StartOrderPaymentAction::class);
        /** @var HandlePaymentWebhookAction $handlePaymentWebhookAction */
        $handlePaymentWebhookAction = app(HandlePaymentWebhookAction::class);
        /** @var UpdateShipmentStatusAction $updateShipmentStatusAction */
        $updateShipmentStatusAction = app(UpdateShipmentStatusAction::class);

        $packageOrder = Order::query()->updateOrCreate(
            ['uuid' => 'digital-order-demo-package-100002'],
            [
                'student_id' => $student->id,
                'kind' => OrderKind::Digital,
                'status' => OrderStatus::Fulfilled,
                'subtotal_amount' => 399,
                'total_amount' => 399,
                'currency' => 'EGP',
                'placed_at' => now()->subDays(12),
            ],
        );

        $packageOrderItem = OrderItem::query()->firstOrCreate(
            [
                'order_id' => $packageOrder->id,
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

        $entitlementGrantor->grant([
            'order' => $packageOrder->fresh('items.product.package'),
            'granted_at' => now()->subDays(12),
            'audit' => false,
        ]);

        $lectureOrder = Order::query()->updateOrCreate(
            ['uuid' => 'digital-order-demo-lecture-100002'],
            [
                'student_id' => $student->id,
                'kind' => OrderKind::Digital,
                'status' => OrderStatus::Fulfilled,
                'subtotal_amount' => 140,
                'total_amount' => 140,
                'currency' => 'EGP',
                'placed_at' => now()->subDays(6),
            ],
        );

        $lectureOrderItem = OrderItem::query()->firstOrCreate(
            [
                'order_id' => $lectureOrder->id,
                'product_name_snapshot' => $lectureProduct?->name_ar ?? 'قوانين نيوتن الأساسية',
            ],
            [
                'product_id' => $lectureProduct?->id,
                'product_kind' => $lectureProduct?->kind?->value ?? 'lecture',
                'quantity' => 1,
                'unit_price_amount' => 140,
                'total_price_amount' => 140,
                'meta' => null,
            ],
        );

        $entitlementGrantor->grant([
            'order' => $lectureOrder->fresh('items.product.package'),
            'granted_at' => now()->subDays(6),
            'audit' => false,
        ]);

        Entitlement::query()->updateOrCreate(
            [
                'student_id' => $student->id,
                'product_id' => $reviewProduct?->id,
                'source' => EntitlementSource::AdminGrant,
            ],
            [
                'order_item_id' => null,
                'status' => 'active',
                'item_name_snapshot' => $reviewProduct?->name_ar ?? 'مراجعة أساسيات الكهرباء',
                'price_amount' => 0,
                'currency' => 'EGP',
                'granted_by_admin_id' => $owner?->id,
                'granted_at' => now()->subDays(2),
                'starts_at' => now()->subDays(2),
                'ends_at' => now()->addDays(28),
                'meta' => ['note' => 'منحة إدارية لمراجعة'],
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

        $cart = Cart::query()->firstOrCreate(
            ['student_id' => $student->id],
            ['currency' => 'EGP'],
        );

        if ($bookProduct) {
            CartItem::query()->updateOrCreate(
                [
                    'cart_id' => $cart->id,
                    'product_id' => $bookProduct->id,
                ],
                [
                    'quantity' => 2,
                    'unit_price_amount' => $bookProduct->price_amount,
                    'total_price_amount' => $bookProduct->price_amount * 2,
                    'meta' => ['product_kind' => $bookProduct->kind->value],
                ],
            );
        }

        if ($packageProduct) {
            CartItem::query()
                ->where('cart_id', $cart->id)
                ->where('product_id', $packageProduct->id)
                ->delete();
        }

        if ($problemSolvingProduct) {
            CartItem::query()->updateOrCreate(
                [
                    'cart_id' => $cart->id,
                    'product_id' => $problemSolvingProduct->id,
                ],
                [
                    'quantity' => 1,
                    'unit_price_amount' => $problemSolvingProduct->price_amount,
                    'total_price_amount' => $problemSolvingProduct->price_amount,
                    'meta' => ['product_kind' => $problemSolvingProduct->kind->value],
                ],
            );
        }

        if ($problemSolvingProduct) {
            $pendingDigitalOrder = Order::query()->firstOrCreate(
                ['uuid' => 'digital-order-demo-pending-100002'],
                [
                    'student_id' => $student->id,
                    'kind' => OrderKind::Digital,
                    'status' => OrderStatus::Draft,
                    'subtotal_amount' => $problemSolvingProduct->price_amount,
                    'total_amount' => $problemSolvingProduct->price_amount,
                    'currency' => 'EGP',
                    'placed_at' => null,
                    'meta' => null,
                ],
            );

            OrderItem::query()->firstOrCreate(
                [
                    'order_id' => $pendingDigitalOrder->id,
                    'product_name_snapshot' => $problemSolvingProduct->name_ar,
                ],
                [
                    'product_id' => $problemSolvingProduct->id,
                    'product_kind' => $problemSolvingProduct->kind->value,
                    'quantity' => 1,
                    'unit_price_amount' => $problemSolvingProduct->price_amount,
                    'total_price_amount' => $problemSolvingProduct->price_amount,
                    'meta' => null,
                ],
            );

            if (! $pendingDigitalOrder->payments()->exists()) {
                $startOrderPaymentAction->execute(
                    $pendingDigitalOrder->fresh(['student', 'items.product.book', 'payments']),
                    ['provider' => 'fake'],
                    $owner,
                );
            }
        }

        if ($reviewProduct) {
            $paidDigitalOrder = Order::query()->firstOrCreate(
                ['uuid' => 'digital-order-demo-paid-100002'],
                [
                    'student_id' => $student->id,
                    'kind' => OrderKind::Digital,
                    'status' => OrderStatus::Draft,
                    'subtotal_amount' => $reviewProduct->price_amount,
                    'total_amount' => $reviewProduct->price_amount,
                    'currency' => 'EGP',
                    'placed_at' => null,
                    'meta' => null,
                ],
            );

            OrderItem::query()->firstOrCreate(
                [
                    'order_id' => $paidDigitalOrder->id,
                    'product_name_snapshot' => $reviewProduct->name_ar,
                ],
                [
                    'product_id' => $reviewProduct->id,
                    'product_kind' => $reviewProduct->kind->value,
                    'quantity' => 1,
                    'unit_price_amount' => $reviewProduct->price_amount,
                    'total_price_amount' => $reviewProduct->price_amount,
                    'meta' => null,
                ],
            );

            if (! $paidDigitalOrder->payments()->where('status', PaymentStatus::Paid->value)->exists()) {
                $payment = $startOrderPaymentAction->execute(
                    $paidDigitalOrder->fresh(['student', 'items.product.book', 'payments']),
                    ['provider' => 'fake'],
                    $owner,
                );

                $handlePaymentWebhookAction->execute('fake', [
                    'event_key' => 'seed_paid_digital_'.$paidDigitalOrder->id,
                    'provider_reference' => $payment->provider_reference,
                    'provider_transaction_reference' => 'txn_seed_digital_'.$paidDigitalOrder->id,
                    'status' => PaymentStatus::Paid->value,
                    'meta' => [
                        'note' => 'Seeded digital payment confirmation.',
                        'trigger' => 'seeder',
                    ],
                    'payload' => [
                        'source' => 'StudentPortalSeeder',
                        'order_uuid' => $paidDigitalOrder->uuid,
                    ],
                ], $owner);
            }
        }

        if ($bookProduct) {
            $shippingBookOrder = Order::query()->firstOrCreate(
                ['uuid' => 'book-order-demo-shipping-100002'],
                [
                    'student_id' => $student->id,
                    'kind' => OrderKind::Book,
                    'status' => OrderStatus::Draft,
                    'subtotal_amount' => $bookProduct->price_amount,
                    'total_amount' => $bookProduct->price_amount,
                    'currency' => 'EGP',
                    'placed_at' => null,
                    'meta' => null,
                ],
            );

            OrderItem::query()->firstOrCreate(
                [
                    'order_id' => $shippingBookOrder->id,
                    'product_name_snapshot' => $bookProduct->name_ar,
                ],
                [
                    'product_id' => $bookProduct->id,
                    'product_kind' => $bookProduct->kind->value,
                    'quantity' => 1,
                    'unit_price_amount' => $bookProduct->price_amount,
                    'total_price_amount' => $bookProduct->price_amount,
                    'meta' => null,
                ],
            );

            if (! $shippingBookOrder->payments()->where('status', PaymentStatus::Paid->value)->exists()) {
                $payment = $startOrderPaymentAction->execute(
                    $shippingBookOrder->fresh(['student', 'items.product.book', 'payments']),
                    [
                        'provider' => 'fake',
                        'shipping' => [
                            'recipient_name' => $student->name,
                            'phone' => $student->phone,
                            'alternate_phone' => $student->parent_phone,
                            'governorate' => 'القاهرة',
                            'city' => 'مدينة نصر',
                            'address_line1' => 'شارع مصطفى النحاس',
                            'address_line2' => 'الدور الثاني',
                            'landmark' => 'بجوار سيتي ستارز',
                        ],
                    ],
                    $owner,
                );

                $handlePaymentWebhookAction->execute('fake', [
                    'event_key' => 'seed_paid_book_'.$shippingBookOrder->id,
                    'provider_reference' => $payment->provider_reference,
                    'provider_transaction_reference' => 'txn_seed_book_'.$shippingBookOrder->id,
                    'status' => PaymentStatus::Paid->value,
                    'meta' => [
                        'note' => 'Seeded book payment confirmation.',
                        'trigger' => 'seeder',
                    ],
                    'payload' => [
                        'source' => 'StudentPortalSeeder',
                        'order_uuid' => $shippingBookOrder->uuid,
                    ],
                ], $owner);
            }

            $shipment = $shippingBookOrder->fresh('shipment')->shipment;

            if ($shipment && $shipment->status === ShipmentStatus::Pending) {
                $updateShipmentStatusAction->execute($shipment, ShipmentStatus::Prepared, $owner, [
                    'carrier_name' => 'شحن المنصة',
                ]);
            }

            $shipment = $shippingBookOrder->fresh('shipment')->shipment;

            if ($shipment && $shipment->status === ShipmentStatus::Prepared) {
                $updateShipmentStatusAction->execute($shipment, ShipmentStatus::InTransit, $owner, [
                    'carrier_name' => 'شحن المنصة',
                    'carrier_reference' => 'SHIP-DEMO-'.$shippingBookOrder->id,
                ]);
            }
        }

        $sessions = AttendanceSession::query()->orderBy('starts_at')->get();

        foreach ($sessions as $index => $session) {
            $attendanceRecorder->record([
                'session' => $session,
                'actor' => $owner,
                'records' => [[
                    'student_id' => $student->id,
                    'attendance_status' => ($index === 0 ? AttendanceStatus::Present : AttendanceStatus::Late)->value,
                    'exam_status_label' => $session->session_type === 'exam' ? 'تم الاختبار' : null,
                    'score' => $session->session_type === 'exam' ? 17 : null,
                    'max_score' => $session->session_type === 'exam' ? 20 : null,
                    'notes' => null,
                ]],
            ]);

            AttendanceRecord::query()
                ->where('attendance_session_id', $session->id)
                ->where('student_id', $student->id)
                ->update(['recorded_at' => $session->starts_at]);
        }

        foreach ([
            [ComplaintType::Complaint, 'أحتاج توضيحًا بخصوص موعد تفعيل الباقة.', now()->subDays(5)],
            [ComplaintType::Suggestion, 'أتمنى إضافة ملخص أسبوعي داخل لوحة الطالب.', now()->subDay()],
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

        $thread = ForumThread::query()->updateOrCreate(
            [
                'student_id' => $student->id,
                'title' => 'سؤال عن قوانين نيوتن',
            ],
            [
                'status' => ForumThreadStatus::Answered,
                'visibility' => ForumVisibility::Public,
                'last_activity_at' => now()->subHours(6),
                'answered_at' => now()->subHours(6),
            ],
        );

        $studentMessage = $thread->messages()->firstOrCreate(
            [
                'author_type' => $student->getMorphClass(),
                'author_id' => $student->id,
                'body' => 'ما الفرق بين القوة المحصلة والقوة المؤثرة عند حل المسألة؟',
            ],
            [
                'is_staff_reply' => false,
            ],
        );

        $studentMessage->attachments()->firstOrCreate(
            ['path' => 'forum/demo-question-image.jpg'],
            [
                'type' => 'image',
                'disk' => 'public',
                'original_name' => 'question-image.jpg',
                'mime_type' => 'image/jpeg',
                'size' => 2048,
            ],
        );

        $thread->messages()->firstOrCreate(
            [
                'author_type' => $owner?->getMorphClass(),
                'author_id' => $owner?->id,
                'body' => 'ابدأ بتحديد الجسم أولًا ثم اجمع القوى المؤثرة عليه قبل التعويض في القانون.',
            ],
            [
                'is_staff_reply' => true,
            ],
        );

        ForumThread::query()->updateOrCreate(
            [
                'student_id' => $pendingStudent->id,
                'title' => 'هل توجد مراجعة مجانية هذا الأسبوع؟',
            ],
            [
                'status' => ForumThreadStatus::Open,
                'visibility' => ForumVisibility::Public,
                'last_activity_at' => now()->subHours(2),
                'answered_at' => null,
            ],
        )->messages()->firstOrCreate(
            [
                'author_type' => $pendingStudent->getMorphClass(),
                'author_id' => $pendingStudent->id,
                'body' => 'أرغب في معرفة ما إذا كانت هناك مراجعة مجانية أستطيع حضورها قبل التفعيل الكامل.',
            ],
            [
                'is_staff_reply' => false,
            ],
        );

        $technicalSupportTeam = SupportTeam::query()->updateOrCreate(
            ['name' => 'الدعم التقني'],
            [
                'description' => 'فريق متابعة مشاكل الدخول، التشغيل، وتفعيل المحتوى.',
                'is_active' => true,
            ],
        );

        $studentCareTeam = SupportTeam::query()->updateOrCreate(
            ['name' => 'متابعة شؤون الطلاب'],
            [
                'description' => 'فريق متابعة الاستفسارات التنظيمية وتنسيق الخدمة للطلاب.',
                'is_active' => true,
            ],
        );

        if ($owner) {
            $technicalSupportTeam->admins()->syncWithoutDetaching([$owner->id]);
            $studentCareTeam->admins()->syncWithoutDetaching([$owner->id]);
        }

        if ($supportAgent) {
            $technicalSupportTeam->admins()->syncWithoutDetaching([$supportAgent->id]);
            $studentCareTeam->admins()->syncWithoutDetaching([$supportAgent->id]);
        }

        $technicalIssueType = SupportTicketType::query()->updateOrCreate(
            ['name' => 'مشكلة تقنية'],
            [
                'default_team_id' => $technicalSupportTeam->id,
                'description' => 'مشكلات فتح المحاضرات أو الوصول أو أخطاء النظام.',
                'is_active' => true,
            ],
        );

        $accountFollowupType = SupportTicketType::query()->updateOrCreate(
            ['name' => 'استفسار حسابي أو تنظيمي'],
            [
                'default_team_id' => $studentCareTeam->id,
                'description' => 'استفسارات التفعيل، المتابعة، أو الترتيبات الإدارية.',
                'is_active' => true,
            ],
        );

        $technicalTicket = SupportTicket::query()->updateOrCreate(
            [
                'student_id' => $student->id,
                'support_ticket_type_id' => $technicalIssueType->id,
                'subject' => 'تعذر فتح المحاضرة المفعلة من الحساب',
            ],
            [
                'support_team_id' => $technicalSupportTeam->id,
                'assigned_admin_id' => $supportAgent?->id ?? $owner?->id,
                'status' => TicketStatus::WaitingCustomer,
                'last_activity_at' => now()->subHours(5),
                'resolved_at' => null,
                'closed_at' => null,
            ],
        );

        $technicalTicket->replies()->firstOrCreate(
            [
                'author_type' => $student->getMorphClass(),
                'author_id' => $student->id,
                'body' => 'لا تظهر لي المحاضرة بعد الدفع رغم أن الطلب مكتمل داخل الحساب.',
            ],
            [
                'is_staff_reply' => false,
                'created_at' => now()->subHours(9),
                'updated_at' => now()->subHours(9),
            ],
        );

        $technicalTicket->replies()->firstOrCreate(
            [
                'author_type' => ($supportAgent ?? $owner)?->getMorphClass(),
                'author_id' => ($supportAgent ?? $owner)?->id,
                'body' => 'راجعنا التفعيل وجرى تحديث الصلاحية. جرّب تسجيل الخروج ثم الدخول مجددًا وأخبرنا بالنتيجة.',
            ],
            [
                'is_staff_reply' => true,
                'created_at' => now()->subHours(5),
                'updated_at' => now()->subHours(5),
            ],
        );

        $followupTicket = SupportTicket::query()->updateOrCreate(
            [
                'student_id' => $pendingStudent->id,
                'support_ticket_type_id' => $accountFollowupType->id,
                'subject' => 'متى يتم مراجعة طلبي بعد التسجيل؟',
            ],
            [
                'support_team_id' => $studentCareTeam->id,
                'assigned_admin_id' => null,
                'status' => TicketStatus::Open,
                'last_activity_at' => now()->subHours(2),
                'resolved_at' => null,
                'closed_at' => null,
            ],
        );

        $followupTicket->replies()->firstOrCreate(
            [
                'author_type' => $pendingStudent->getMorphClass(),
                'author_id' => $pendingStudent->id,
                'body' => 'أحتاج معرفة المدة المتوقعة لمراجعة الحساب وتفعيل الوصول الأولي.',
            ],
            [
                'is_staff_reply' => false,
                'created_at' => now()->subHours(2),
                'updated_at' => now()->subHours(2),
            ],
        );

        if ($lecture) {
            MistakeItem::query()->updateOrCreate(
                [
                    'student_id' => $student->id,
                    'lecture_id' => $lecture->id,
                    'question_reference' => 'NEWTON-01',
                ],
                [
                    'exam_id' => null,
                    'question_text' => 'جسم يتحرك بعجلة ثابتة، ما القانون الأنسب لحساب القوة المحصلة؟',
                    'correct_answer_snapshot' => 'القوة المحصلة = الكتلة × العجلة.',
                    'model_answer_snapshot' => 'ابدأ بتحديد الكتلة والعجلة ثم استخدم قانون نيوتن الثاني مباشرة.',
                    'explanation' => 'الخطأ هنا كان استخدام قانون السرعة بدل قانون القوة، مع أن المطلوب مرتبط بالعجلة والقوة المحصلة.',
                    'image_path' => null,
                    'score_lost' => 2,
                    'score_meta' => ['max_score' => 5],
                    'source' => 'seeded_demo',
                    'meta' => ['from' => 'manual_review'],
                ],
            );
        }

        if ($reviewLecture) {
            MistakeItem::query()->updateOrCreate(
                [
                    'student_id' => $student->id,
                    'lecture_id' => $reviewLecture->id,
                    'question_reference' => 'ELEC-REVIEW-02',
                ],
                [
                    'exam_id' => null,
                    'question_text' => 'ما سبب زيادة شدة التيار عند ثبات المقاومة وارتفاع الجهد؟',
                    'correct_answer_snapshot' => 'لأن شدة التيار تتناسب طرديًا مع الجهد عند ثبات المقاومة.',
                    'model_answer_snapshot' => 'من قانون أوم: شدة التيار = الجهد / المقاومة.',
                    'explanation' => 'تم الخلط بين العلاقة الطردية والعكسية. راجع قانون أوم قبل إعادة الحل.',
                    'image_path' => null,
                    'score_lost' => 3,
                    'score_meta' => ['max_score' => 5],
                    'source' => 'seeded_demo',
                    'meta' => ['from' => 'manual_review'],
                ],
            );
        }

        if ($lecture) {
            $lectureProgressService->touchOpen($student, $lecture);
            $lectureProgressService->updateProgress($student, $lecture->loadMissing('checkpoints'), [
                'position_seconds' => 1500,
                'consumed_seconds' => 1500,
            ]);

            $checkpoint = $lecture->checkpoints()->orderBy('sort_order')->first();

            if ($checkpoint) {
                $lectureProgressService->reachCheckpoint($student, $lecture, $checkpoint);
            }
        }

        if ($freeLecture) {
            $lectureProgressService->touchOpen($student, $freeLecture);
            $lectureProgressService->markCompleted($student, $freeLecture);
        }
    }
}
