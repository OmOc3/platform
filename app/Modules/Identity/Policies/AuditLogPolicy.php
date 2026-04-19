<?php

namespace App\Modules\Identity\Policies;

use App\Modules\Identity\Models\Admin;
use App\Modules\Identity\Models\AuditLog;

class AuditLogPolicy
{
    public function viewAny(Admin $admin): bool
    {
        return $admin->hasPermissionTo('audit-logs.view', 'admin');
    }

    public function view(Admin $admin, AuditLog $auditLog): bool
    {
        return $admin->hasPermissionTo('audit-logs.view', 'admin');
    }
}
