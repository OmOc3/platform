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
                'message' => 'يمكن شراء هذه الباقة مباشرة.',
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
                'message' => 'لا يمكن شراء هذه الباقة لأن لديك بالفعل وصولًا لبعض عناصرها.',
                'overlaps' => $overlaps,
            ];
        }

        return [
            'eligible' => true,
            'state' => 'eligible',
            'message' => $overlaps === [] ? 'يمكن شراء هذه الباقة مباشرة.' : 'يمكن شراء الباقة، لكن لديك بالفعل وصولًا لبعض عناصرها.',
            'overlaps' => $overlaps,
        ];
    }
}
