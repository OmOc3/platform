<?php

namespace App\Modules\Academic\Queries;

use App\Modules\Academic\Models\Lecture;
use App\Modules\Academic\Models\LectureProgress;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class LectureProgressIndexQuery
{
    public function builder(Lecture $lecture, Request $request): Builder
    {
        return LectureProgress::query()
            ->with(['student', 'lastCheckpoint'])
            ->where('lecture_id', $lecture->id)
            ->when($request->filled('search'), function (Builder $query) use ($request): void {
                $search = trim((string) $request->input('search'));

                $query->whereHas('student', function (Builder $builder) use ($search): void {
                    $builder->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('student_number', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('status'), function (Builder $query) use ($request): void {
                $status = $request->string('status')->toString();

                if ($status === 'completed') {
                    $query->whereNotNull('completed_at');
                }

                if ($status === 'started') {
                    $query->whereNull('completed_at');
                }
            })
            ->orderByDesc('completion_percent')
            ->orderByDesc('last_opened_at')
            ->orderByDesc('id');
    }
}
