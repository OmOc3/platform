<?php

namespace App\Modules\Academic\Queries;

use App\Modules\Academic\Models\Lecture;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class LecturesIndexQuery
{
    public function builder(Request $request): Builder
    {
        $search = $request->string('search')->toString();
        $status = $request->string('status')->toString();
        $type = $request->string('type')->toString();
        $gradeId = $request->integer('grade_id');

        return Lecture::query()
            ->with(['grade', 'track', 'curriculumSection', 'lectureSection', 'product'])
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $builder) use ($search): void {
                    $builder->where('title', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%")
                        ->orWhere('short_description', 'like', "%{$search}%");
                });
            })
            ->when($status !== '', fn (Builder $query) => $query->where('is_active', $status === 'active'))
            ->when($type !== '', fn (Builder $query) => $query->where('type', $type))
            ->when($gradeId > 0, fn (Builder $query) => $query->where('grade_id', $gradeId))
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->orderBy('title');
    }
}
