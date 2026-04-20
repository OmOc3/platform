<?php

namespace App\Modules\Commerce\Actions\Packages;

use App\Modules\Academic\Models\Lecture;
use App\Modules\Commerce\Models\Entitlement;
use App\Modules\Commerce\Models\Package;
use App\Modules\Students\Models\Student;

class EvaluatePackageEligibilityAction
{
    /**
     * @return array{eligible: bool, state: string, message: string, overlaps: array<int, string>}
     */
    public function execute(Student $student, Package $package): array
    {
        $package->loadMissing(['items.item', 'product']);

        $rule = $package->metadata['overlap_rule'] ?? 'block';
        $alreadyPurchased = Entitlement::query()
            ->where('student_id', $student->id)
            ->where('product_id', $package->product_id)
            ->where('status', 'active')
            ->where(function ($query): void {
                $query->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($query): void {
                $query->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            })
            ->exists();

        if ($alreadyPurchased) {
            return [
                'eligible' => false,
                'state' => 'already_owned',
                'message' => 'تم شراء العرض بالفعل على هذا الحساب.',
                'overlaps' => [],
            ];
        }

        $lectureIds = $package->items
            ->where('item_type', Lecture::class)
            ->pluck('item_id')
            ->filter()
            ->unique()
            ->values();

        if ($lectureIds->isEmpty()) {
            return [
                'eligible' => true,
                'state' => 'eligible',
                'message' => 'يمكنك شراء هذه الباقة مباشرة.',
                'overlaps' => [],
            ];
        }

        $directLectureTitles = Entitlement::query()
            ->where('student_id', $student->id)
            ->where('status', 'active')
            ->whereHas('product.lecture', fn ($query) => $query->whereIn('lectures.id', $lectureIds))
            ->with('product.lecture')
            ->get()
            ->map(fn (Entitlement $entitlement): ?string => $entitlement->product?->lecture?->title)
            ->filter()
            ->values();

        $packageLectureTitles = Entitlement::query()
            ->where('student_id', $student->id)
            ->where('status', 'active')
            ->whereHas('product.package.items', fn ($query) => $query
                ->where('item_type', Lecture::class)
                ->whereIn('item_id', $lectureIds))
            ->with('product.package.items.item')
            ->get()
            ->flatMap(fn (Entitlement $entitlement) => $entitlement->product?->package?->items
                ?->where('item_type', Lecture::class)
                ->whereIn('item_id', $lectureIds)
                ->map(fn ($item) => $item->item?->title ?? $item->item_name_snapshot)
                ?? collect())
            ->filter()
            ->values();

        $overlaps = $directLectureTitles
            ->merge($packageLectureTitles)
            ->unique()
            ->values()
            ->all();

        if ($overlaps !== [] && $rule === 'block') {
            return [
                'eligible' => false,
                'state' => 'blocked',
                'message' => 'لا يمكنك شراء الباقة لأنك قمت بشراء بعض المحاضرات الموجودة بها بالفعل.',
                'overlaps' => $overlaps,
            ];
        }

        return [
            'eligible' => true,
            'state' => 'eligible',
            'message' => $overlaps === []
                ? 'يمكنك شراء هذه الباقة مباشرة.'
                : 'يمكنك شراء الباقة، مع الاحتفاظ بالعناصر المفعلة لديك بالفعل.',
            'overlaps' => $overlaps,
        ];
    }
}
