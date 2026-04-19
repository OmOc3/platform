<?php

namespace App\Modules\Identity\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Identity\Actions\Admins\CreateAdminAction;
use App\Modules\Identity\Actions\Admins\UpdateAdminAction;
use App\Modules\Identity\Http\Requests\Admin\Admins\StoreAdminRequest;
use App\Modules\Identity\Http\Requests\Admin\Admins\UpdateAdminRequest;
use App\Modules\Identity\Models\Admin;
use App\Modules\Identity\Queries\AdminsIndexQuery;
use App\Shared\Contracts\AuditLogger;
use App\Shared\Support\Exports\CsvExporter;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class AdminController extends Controller
{
    public function __construct(
        private readonly AdminsIndexQuery $adminsIndexQuery,
        private readonly CreateAdminAction $createAdminAction,
        private readonly UpdateAdminAction $updateAdminAction,
        private readonly AuditLogger $auditLogger,
    ) {
    }

    public function index(Request $request): View|\Symfony\Component\HttpFoundation\StreamedResponse
    {
        $this->authorize('viewAny', Admin::class);

        $query = $this->adminsIndexQuery->builder($request);

        if ($request->string('export')->toString() === 'csv') {
            return CsvExporter::download('admins.csv', ['الاسم', 'البريد', 'المنصب', 'الحالة'], $query->get()
                ->map(fn (Admin $admin): array => [
                    $admin->name,
                    $admin->email,
                    $admin->job_title,
                    $admin->is_active ? 'نشط' : 'متوقف',
                ])
                ->all());
        }

        return view('admin.identity.admins.index', [
            'admins' => $query->paginate(12)->withQueryString(),
            'roles' => Role::query()->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Admin::class);

        return view('admin.identity.admins.create', [
            'adminUser' => new Admin(),
            'roles' => Role::query()->orderBy('name')->get(),
        ]);
    }

    public function store(StoreAdminRequest $request): RedirectResponse
    {
        $this->authorize('create', Admin::class);

        $this->createAdminAction->execute($request->validated(), auth('admin')->user());

        return redirect()
            ->route('admin.admins.index')
            ->with('status', 'تم إنشاء المشرف بنجاح.');
    }

    public function edit(Admin $admin): View
    {
        $this->authorize('update', $admin);

        return view('admin.identity.admins.edit', [
            'adminUser' => $admin->load('roles'),
            'roles' => Role::query()->orderBy('name')->get(),
        ]);
    }

    public function update(UpdateAdminRequest $request, Admin $admin): RedirectResponse
    {
        $this->authorize('update', $admin);

        $this->updateAdminAction->execute($admin, $request->validated(), auth('admin')->user());

        return redirect()
            ->route('admin.admins.index')
            ->with('status', 'تم تحديث بيانات المشرف.');
    }

    public function destroy(Admin $admin): RedirectResponse
    {
        $this->authorize('delete', $admin);

        $oldValues = $admin->toArray();
        $admin->delete();

        $this->auditLogger->log(
            event: 'identity.admin.deleted',
            actor: auth('admin')->user(),
            subject: $admin,
            oldValues: $oldValues,
        );

        return redirect()
            ->route('admin.admins.index')
            ->with('status', 'تم حذف المشرف.');
    }
}
