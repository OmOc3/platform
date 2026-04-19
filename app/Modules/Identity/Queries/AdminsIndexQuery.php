<?php

namespace App\Modules\Identity\Queries;

use App\Modules\Identity\Models\Admin;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class AdminsIndexQuery
{
    public function builder(Request $request): Builder
    {
        $search = $request->string('search')->toString();
        $role = $request->string('role')->toString();

        return Admin::query()
            ->with('roles')
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $builder) use ($search): void {
                    $builder->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('job_title', 'like', "%{$search}%");
                });
            })
            ->when($role !== '', function (Builder $query) use ($role): void {
                $query->whereHas('roles', fn (Builder $builder) => $builder->where('name', $role));
            })
            ->orderByDesc('created_at');
    }
}
