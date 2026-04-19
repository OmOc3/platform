<?php

namespace App\Modules\Commerce\Queries;

use App\Modules\Commerce\Actions\Packages\EvaluatePackageEligibilityAction;
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
            ]);
    }
}
