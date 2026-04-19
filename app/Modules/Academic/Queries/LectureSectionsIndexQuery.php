<?php

namespace App\Modules\Academic\Queries;

use App\Modules\Academic\Models\LectureSection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class LectureSectionsIndexQuery
{
    public function builder(Request $request): Builder
    {
        $search = $request->string('search')->toString();
        $status = $request->string('status')->toString();
        $gradeId = $request->integer('grade_id');
        $curriculumSectionId = $request->integer('curriculum_section_id');

        return LectureSection::query()
            ->with(['grade', 'track', 'curriculumSection'])
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $builder) use ($search): void {
                    $builder->where('name_ar', 'like', "%{$search}%")
                        ->orWhere('name_en', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%");
                });
            })
            ->when($status !== '', fn (Builder $query) => $query->where('is_active', $status === 'active'))
            ->when($gradeId > 0, fn (Builder $query) => $query->where('grade_id', $gradeId))
            ->when($curriculumSectionId > 0, fn (Builder $query) => $query->where('curriculum_section_id', $curriculumSectionId))
            ->orderBy('sort_order')
            ->orderBy('name_ar');
    }
}
