<?php

namespace App\Modules\Academic\Queries;

use App\Modules\Academic\Models\Track;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class TracksIndexQuery
{
    public function builder(Request $request): Builder
    {
        $search = $request->string('search')->toString();
        $gradeId = $request->integer('grade_id');
        $status = $request->string('status')->toString();

        return Track::query()
            ->with('grade')
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $builder) use ($search): void {
                    $builder->where('name_ar', 'like', "%{$search}%")
                        ->orWhere('name_en', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                });
            })
            ->when($gradeId > 0, fn (Builder $query) => $query->where('grade_id', $gradeId))
            ->when($status !== '', fn (Builder $query) => $query->where('is_active', $status === 'active'))
            ->orderBy('sort_order')
            ->orderBy('name_ar');
    }
}
