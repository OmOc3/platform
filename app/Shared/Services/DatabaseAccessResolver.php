<?php

namespace App\Shared\Services;

use App\Modules\Academic\Models\Exam;
use App\Modules\Academic\Models\Lecture;
use App\Modules\Commerce\Models\Entitlement;
use App\Modules\Commerce\Models\PackageItem;
use App\Modules\Students\Models\Student;
use App\Shared\Contracts\AccessResolver;
use App\Shared\Enums\ContentAccessState;
use App\Shared\Enums\EntitlementSource;
use Illuminate\Database\Eloquent\Model;

class DatabaseAccessResolver implements AccessResolver
{
    public function hasAccess(Student $student, string $resourceType, int|string $resourceId): bool
    {
        return match ($resourceType) {
            'lecture' => Lecture::query()
                ->whereKey($resourceId)
                ->get()
                ->contains(fn (Lecture $lecture): bool => in_array($this->resolveLectureState($student, $lecture)['state'], [
                    ContentAccessState::Open,
                    ContentAccessState::Free,
                    ContentAccessState::OwnedViaEntitlement,
                ], true)),
            'exam' => Exam::query()
                ->whereKey($resourceId)
                ->get()
                ->contains(fn (Exam $exam): bool => in_array($this->resolveExamState($student, $exam)['state'], [
                    ContentAccessState::Open,
                    ContentAccessState::Free,
                    ContentAccessState::OwnedViaEntitlement,
                ], true)),
            default => false,
        };
    }

    public function resolveState(Student $student, object $resource): array
    {
        return match (true) {
            $resource instanceof Lecture => $this->resolveLectureState($student, $resource),
            $resource instanceof Exam => $this->resolveExamState($student, $resource),
            default => [
                'state' => ContentAccessState::Unavailable,
                'label' => 'غير متاح',
                'reason' => 'لا يمكن تحديد حالة الوصول لهذا العنصر الآن.',
                'entitlement' => null,
            ],
        };
    }

    /**
     * @return array{state: ContentAccessState, label: string, reason: ?string, entitlement: ?Entitlement}
     */
    private function resolveLectureState(Student $student, Lecture $lecture): array
    {
        if (! $lecture->is_active || ($lecture->published_at && $lecture->published_at->isFuture())) {
            return [
                'state' => ContentAccessState::Unavailable,
                'label' => 'غير متاح الآن',
                'reason' => 'سيظهر هذا المحتوى بعد النشر.',
                'entitlement' => null,
            ];
        }

        if ($lecture->is_free) {
            return [
                'state' => ContentAccessState::Free,
                'label' => 'مفتوح مجانًا',
                'reason' => 'هذا المحتوى مجاني لكل الطلاب.',
                'entitlement' => null,
            ];
        }

        $entitlement = $lecture->product?->entitlements()
            ->where('student_id', $student->id)
            ->where('status', 'active')
            ->where(function ($query): void {
                $query->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($query): void {
                $query->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            })
            ->latest('granted_at')
            ->first();

        if ($entitlement instanceof Entitlement) {
            return [
                'state' => ContentAccessState::OwnedViaEntitlement,
                'label' => $entitlement->source === EntitlementSource::Free ? 'مفتوح مجانًا' : 'افتح المحتوى',
                'reason' => null,
                'entitlement' => $entitlement,
            ];
        }

        $packageEntitlement = Entitlement::query()
            ->where('student_id', $student->id)
            ->where('status', 'active')
            ->whereHas('product.package.items', fn ($query) => $query
                ->where('item_type', Lecture::class)
                ->where('item_id', $lecture->id))
            ->where(function ($query): void {
                $query->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($query): void {
                $query->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            })
            ->latest('granted_at')
            ->first();

        if ($packageEntitlement instanceof Entitlement) {
            return [
                'state' => ContentAccessState::OwnedViaEntitlement,
                'label' => 'افتح المحتوى',
                'reason' => 'تم تفعيل هذا المحتوى عبر باقة مرتبطة.',
                'entitlement' => $packageEntitlement,
            ];
        }

        $includedPackageCount = PackageItem::query()
            ->where('item_type', Lecture::class)
            ->where('item_id', $lecture->id)
            ->count();

        if ($includedPackageCount > 0) {
            return [
                'state' => ContentAccessState::IncludedInPackage,
                'label' => 'ضمن باقة',
                'reason' => 'يمكن فتح هذا المحتوى عبر باقة مرتبطة به.',
                'entitlement' => null,
            ];
        }

        if ($lecture->product_id) {
            return [
                'state' => ContentAccessState::Buy,
                'label' => 'اشتر الآن',
                'reason' => 'يتطلب هذا المحتوى شراء مباشرًا أو منحة إدارية.',
                'entitlement' => null,
            ];
        }

        return [
            'state' => ContentAccessState::Unavailable,
            'label' => 'غير متاح',
            'reason' => 'هذا المحتوى غير متاح للشراء حاليًا.',
            'entitlement' => null,
        ];
    }

    /**
     * @return array{state: ContentAccessState, label: string, reason: ?string, entitlement: ?Entitlement}
     */
    private function resolveExamState(Student $student, Exam $exam): array
    {
        if (! $exam->is_active || ($exam->published_at && $exam->published_at->isFuture())) {
            return [
                'state' => ContentAccessState::Unavailable,
                'label' => 'قريبًا',
                'reason' => 'سيظهر الاختبار بعد النشر.',
                'entitlement' => null,
            ];
        }

        if ($exam->is_free) {
            return [
                'state' => ContentAccessState::Free,
                'label' => 'ادخل الاختبار',
                'reason' => 'الاختبار مفتوح مجانًا.',
                'entitlement' => null,
            ];
        }

        if ($exam->lecture instanceof Lecture) {
            $lectureState = $this->resolveLectureState($student, $exam->lecture);

            if (in_array($lectureState['state'], [ContentAccessState::Open, ContentAccessState::Free, ContentAccessState::OwnedViaEntitlement], true)) {
                return [
                    'state' => ContentAccessState::Open,
                    'label' => 'افتح الاختبار',
                    'reason' => null,
                    'entitlement' => $lectureState['entitlement'],
                ];
            }

            if ($lectureState['state'] === ContentAccessState::IncludedInPackage) {
                return [
                    'state' => ContentAccessState::IncludedInPackage,
                    'label' => 'ضمن المحتوى المرتبط',
                    'reason' => 'يتاح هذا الاختبار بعد تفعيل المحاضرة أو الباقة المرتبطة به.',
                    'entitlement' => null,
                ];
            }

            if ($lectureState['state'] === ContentAccessState::Buy) {
                return [
                    'state' => ContentAccessState::Buy,
                    'label' => 'اشتر المحاضرة أولًا',
                    'reason' => 'هذا الاختبار مرتبط بمحاضرة مدفوعة.',
                    'entitlement' => null,
                ];
            }
        }

        return [
            'state' => ContentAccessState::Unavailable,
            'label' => 'غير متاح',
            'reason' => 'لا يوجد مسار وصول مفعل لهذا الاختبار بعد.',
            'entitlement' => null,
        ];
    }
}
