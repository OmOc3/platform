<?php

namespace App\Modules\Commerce\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Modules\Commerce\Actions\Packages\EvaluatePackageEligibilityAction;
use App\Modules\Commerce\Models\Package;
use App\Modules\Commerce\Queries\PackageCatalogQuery;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class PackageCatalogController extends Controller
{
    public function __construct(
        private readonly PackageCatalogQuery $packageCatalogQuery,
        private readonly EvaluatePackageEligibilityAction $evaluatePackageEligibilityAction,
    ) {
    }

    public function index(Request $request): View
    {
        $student = auth('student')->user();

        return view('student.catalog.packages.index', [
            'packages' => $this->packageCatalogQuery->paginateFor($student, $request),
        ]);
    }

    public function show(Package $package): View
    {
        $student = auth('student')->user();
        $package->load(['product', 'items.item']);

        return view('student.catalog.packages.show', [
            'package' => $package,
            'eligibility' => $this->evaluatePackageEligibilityAction->execute($student, $package),
        ]);
    }
}
