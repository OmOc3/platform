<?php

namespace App\Modules\Identity\Actions\Settings;

use App\Modules\Identity\Models\Setting;
use App\Shared\Contracts\AuditLogger;

class UpsertSettingAction
{
    public function __construct(private readonly AuditLogger $auditLogger)
    {
    }

    public function execute(array $data, mixed $actor, ?Setting $setting = null): Setting
    {
        $setting ??= new Setting();
        $oldValues = $setting->exists ? $setting->toArray() : [];

        $setting->fill($data);
        $setting->save();

        $this->auditLogger->log(
            event: $setting->wasRecentlyCreated ? 'identity.setting.created' : 'identity.setting.updated',
            actor: $actor,
            subject: $setting,
            oldValues: $oldValues,
            newValues: $setting->fresh()->toArray(),
        );

        return $setting->refresh();
    }
}
