<?php

namespace App\Modules\Academic\Queries;

use App\Modules\Academic\Models\Exam;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ExamsIndexQuery
{
    public function builder(Request $request): Builder
    {
        $search = $request->string('search')->toString();
        $status = $request->string('status')->toString();
        $gradeId = $request->integer('grade_id');

        return Exam::query()
            ->with(['grade', 'track', 'lecture'])
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $builder) use ($search): void {
                    $builder->where('title', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%")
                        ->orWhere('short_description', 'like', "%{$search}%");
                });
            })
            ->when($status !== '', fn (Builder $query) => $query->where('is_active', $status === 'active'))
            ->when($gradeId > 0, fn (Builder $query) => $query->where('grade_id', $gradeId))
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->orderBy('title');
    }
}
