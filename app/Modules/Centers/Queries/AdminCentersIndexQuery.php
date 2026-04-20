<?php

namespace App\Modules\Centers\Queries;

use App\Modules\Centers\Models\EducationalCenter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class AdminCentersIndexQuery
{
    public function builder(Request $request): Builder
    {
        $search = $request->string('search')->toString();
        $status = $request->string('status')->toString();

        return EducationalCenter::query()
            ->with([
                'groups' => fn ($query) => $query
                    ->withCount(['students', 'sessions'])
                    ->orderByDesc('is_active')
                    ->orderBy('name_ar'),
            ])
            ->withCount([
                'groups',
                'students',
                'groups as active_groups_count' => fn (Builder $query) => $query->where('is_active', true),
            ])
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $builder) use ($search): void {
                    $builder->where('name_ar', 'like', "%{$search}%")
                        ->orWhere('city', 'like', "%{$search}%")
                        ->orWhereHas('groups', fn (Builder $groupQuery) => $groupQuery->where('name_ar', 'like', "%{$search}%"));
                });
            })
            ->when($status !== '', fn (Builder $query) => $query->where('is_active', $status === 'active'))
            ->orderByDesc('is_active')
            ->orderBy('name_ar');
    }
}
