<?php

namespace App\Modules\Identity\Queries;

use App\Modules\Identity\Models\AuditLog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class AuditLogsIndexQuery
{
    public function builder(Request $request): Builder
    {
        $search = $request->string('search')->toString();

        return AuditLog::query()
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $builder) use ($search): void {
                    $builder->where('event', 'like', "%{$search}%")
                        ->orWhere('actor_type', 'like', "%{$search}%")
                        ->orWhere('auditable_type', 'like', "%{$search}%");
                });
            })
            ->latest('created_at');
    }
}
