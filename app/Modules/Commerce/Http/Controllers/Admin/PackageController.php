<?php

namespace App\Modules\Commerce\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Academic\Models\Lecture;
use App\Modules\Commerce\Actions\Packages\SavePackageAction;
use App\Modules\Commerce\Http\Requests\Admin\Packages\StorePackageRequest;
use App\Modules\Commerce\Http\Requests\Admin\Packages\UpdatePackageRequest;
use App\Modules\Commerce\Models\Package;
use App\Modules\Commerce\Queries\PackagesIndexQuery;
use App\Shared\Contracts\AuditLogger;
use App\Shared\Support\Exports\CsvExporter;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PackageController extends Controller
{
    public function __construct(
        private readonly PackagesIndexQuery $packagesIndexQuery,
        private readonly SavePackageAction $savePackageAction,
        private readonly AuditLogger $auditLogger,
    ) {
    }

    public function index(Request $request): View|StreamedResponse
    {
        $this->authorize('viewAny', Package::class);

        $query = $this->packagesIndexQuery->builder($request);

        if ($request->string('export')->toString() === 'csv') {
            return CsvExporter::download('packages.csv', ['الباقة', 'الدورة', 'عدد العناصر', 'السعر'], $query->get()
                ->map(fn (Package $package): array => [
                    $package->product?->name_ar ?? '-',
                    $package->billing_cycle_label ?? '-',
                    (string) $package->items->count(),
                    (string) ($package->product?->price_amount ?? 0),
                ])
                ->all());
        }

        return view('admin.commerce.packages.index', [
            'packages' => $query->paginate(15)->withQueryString(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Package::class);

        return view('admin.commerce.packages.create', [
            'package' => new Package(['is_featured' => false]),
            'lectures' => Lecture::query()->orderBy('title')->get(),
        ]);
    }

    public function store(StorePackageRequest $request): RedirectResponse
    {
        $this->authorize('create', Package::class);

        $this->savePackageAction->execute($request->validated(), auth('admin')->user());

        return redirect()
            ->route('admin.packages.index')
            ->with('status', 'تم إنشاء الباقة.');
    }

    public function edit(Package $package): View
    {
        $this->authorize('update', $package);

        return view('admin.commerce.packages.edit', [
            'package' => $package->load(['product', 'items']),
            'lectures' => Lecture::query()->orderBy('title')->get(),
        ]);
    }

    public function update(UpdatePackageRequest $request, Package $package): RedirectResponse
    {
        $this->authorize('update', $package);

        $this->savePackageAction->execute($request->validated(), auth('admin')->user(), $package);

        return redirect()
            ->route('admin.packages.index')
            ->with('status', 'تم تحديث الباقة.');
    }

    public function destroy(Package $package): RedirectResponse
    {
        $this->authorize('delete', $package);

        $oldValues = $package->toArray();
        $package->delete();

        $this->auditLogger->log(
            event: 'commerce.package.deleted',
            actor: auth('admin')->user(),
            subject: $package,
            oldValues: $oldValues,
        );

        return redirect()
            ->route('admin.packages.index')
            ->with('status', 'تم حذف الباقة.');
    }
}
