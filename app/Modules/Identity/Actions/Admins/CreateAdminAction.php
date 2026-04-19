<?php

namespace App\Modules\Identity\Actions\Admins;

use App\Modules\Identity\Models\Admin;
use App\Shared\Contracts\AuditLogger;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class CreateAdminAction
{
    public function __construct(private readonly AuditLogger $auditLogger)
    {
    }

    public function execute(array $data, mixed $actor): Admin
    {
        $roleNames = Arr::pull($data, 'role_names', []);
        $data['uuid'] = (string) Str::uuid();

        $admin = Admin::query()->create($data);
        $admin->syncRoles($roleNames);

        $this->auditLogger->log(
            event: 'identity.admin.created',
            actor: $actor,
            subject: $admin,
            newValues: $admin->fresh()->toArray(),
            meta: ['roles' => $roleNames],
        );

        return $admin;
    }
}
