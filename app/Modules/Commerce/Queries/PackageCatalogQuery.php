<?php

namespace App\Modules\Commerce\Queries;

use App\Modules\Commerce\Actions\Packages\EvaluatePackageEligibilityAction;
use App\Modules\Commerce\Models\CartItem;
use App\Modules\Commerce\Models\Package;
use App\Modules\Students\Models\Student;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class PackageCatalogQuery
{
    public function __construct(private readonly EvaluatePackageEligibilityAction $evaluatePackageEligibilityAction)
    {
    }

    public function paginateFor(Student $student, Request $request): LengthAwarePaginator
    {
        $cartProductIds = CartItem::query()
            ->whereHas('cart', fn ($query) => $query->where('student_id', $student->id))
            ->pluck('product_id')
            ->map(fn ($productId) => (int) $productId)
            ->all();

        return Package::query()
            ->with(['product', 'items.item'])
            ->whereHas('product', fn ($query) => $query->where('is_active', true))
            ->when($request->boolean('featured'), fn ($query) => $query->where('is_featured', true))
            ->orderByDesc('is_featured')
            ->orderBy('lecture_count')
            ->paginate(12)
            ->withQueryString()
            ->through(fn (Package $package): array => [
                'package' => $package,
                'eligibility' => $this->evaluatePackageEligibilityAction->execute($student, $package),
                'group' => $this->groupFor($package),
                'in_cart' => in_array((int) $package->product_id, $cartProductIds, true),
            ]);
    }

    private function groupFor(Package $package): string
    {
        $label = mb_strtolower((string) $package->billing_cycle_label);

        if (str_contains($label, '3') || str_contains($label, 'ثلاث')) {
            return 'quarterly';
        }

        if (str_contains($label, 'شهر')) {
            return 'monthly';
        }

        return 'special';
    }
}
