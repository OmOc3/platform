<?php

namespace App\Modules\Academic\Queries;

use App\Modules\Academic\Models\Grade;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class GradesIndexQuery
{
    public function builder(Request $request): Builder
    {
        $search = $request->string('search')->toString();
        $status = $request->string('status')->toString();

        return Grade::query()
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $builder) use ($search): void {
                    $builder->where('name_ar', 'like', "%{$search}%")
                        ->orWhere('name_en', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                });
            })
            ->when($status !== '', fn (Builder $query) => $query->where('is_active', $status === 'active'))
            ->orderBy('sort_order')
            ->orderBy('name_ar');
    }
}
