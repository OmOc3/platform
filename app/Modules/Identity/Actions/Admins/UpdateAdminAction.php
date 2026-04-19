<?php

namespace App\Modules\Identity\Actions\Admins;

use App\Modules\Identity\Models\Admin;
use App\Shared\Contracts\AuditLogger;
use Illuminate\Support\Arr;

class UpdateAdminAction
{
    public function __construct(private readonly AuditLogger $auditLogger)
    {
    }

    public function execute(Admin $admin, array $data, mixed $actor): Admin
    {
        $oldValues = $admin->toArray();
        $roleNames = Arr::pull($data, 'role_names', []);

        if (($data['password'] ?? null) === null) {
            unset($data['password']);
        }

        $admin->update($data);
        $admin->syncRoles($roleNames);

        $this->auditLogger->log(
            event: 'identity.admin.updated',
            actor: $actor,
            subject: $admin,
            oldValues: $oldValues,
            newValues: $admin->fresh()->toArray(),
            meta: ['roles' => $roleNames],
        );

        return $admin->refresh();
    }
}
