<?php

namespace App\Modules\Identity\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Identity\Actions\Settings\UpsertSettingAction;
use App\Modules\Identity\Enums\SettingType;
use App\Modules\Identity\Http\Requests\Admin\Settings\StoreSettingRequest;
use App\Modules\Identity\Http\Requests\Admin\Settings\UpdateSettingRequest;
use App\Modules\Identity\Models\Setting;
use App\Modules\Identity\Queries\SettingsIndexQuery;
use App\Shared\Contracts\AuditLogger;
use App\Shared\Support\Exports\CsvExporter;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SettingController extends Controller
{
    public function __construct(
        private readonly SettingsIndexQuery $settingsIndexQuery,
        private readonly UpsertSettingAction $upsertSettingAction,
        private readonly AuditLogger $auditLogger,
    ) {
    }

    public function index(Request $request): View|StreamedResponse
    {
        $this->authorize('viewAny', Setting::class);

        $query = $this->settingsIndexQuery->builder($request);

        if ($request->string('export')->toString() === 'csv') {
            return CsvExporter::download('settings.csv', ['المجموعة', 'المفتاح', 'القيمة', 'النوع'], $query->get()
                ->map(fn (Setting $setting): array => [
                    $setting->group,
                    $setting->key,
                    $setting->value,
                    $setting->type->value,
                ])
                ->all());
        }

        return view('admin.identity.settings.index', [
            'settings' => $query->paginate(15)->withQueryString(),
            'settingTypes' => SettingType::cases(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Setting::class);

        return view('admin.identity.settings.create', [
            'setting' => new Setting(['type' => SettingType::String]),
            'settingTypes' => SettingType::cases(),
        ]);
    }

    public function store(StoreSettingRequest $request): RedirectResponse
    {
        $this->authorize('create', Setting::class);

        $this->upsertSettingAction->execute($request->validated(), auth('admin')->user());

        return redirect()
            ->route('admin.settings.index')
            ->with('status', 'تم إنشاء الإعداد بنجاح.');
    }

    public function edit(Setting $setting): View
    {
        $this->authorize('update', $setting);

        return view('admin.identity.settings.edit', [
            'setting' => $setting,
            'settingTypes' => SettingType::cases(),
        ]);
    }

    public function update(UpdateSettingRequest $request, Setting $setting): RedirectResponse
    {
        $this->authorize('update', $setting);

        $this->upsertSettingAction->execute($request->validated(), auth('admin')->user(), $setting);

        return redirect()
            ->route('admin.settings.index')
            ->with('status', 'تم تحديث الإعداد.');
    }

    public function destroy(Setting $setting): RedirectResponse
    {
        $this->authorize('delete', $setting);

        $oldValues = $setting->toArray();
        $setting->delete();

        $this->auditLogger->log(
            event: 'identity.setting.deleted',
            actor: auth('admin')->user(),
            subject: $setting,
            oldValues: $oldValues,
        );

        return redirect()
            ->route('admin.settings.index')
            ->with('status', 'تم حذف الإعداد.');
    }
}
