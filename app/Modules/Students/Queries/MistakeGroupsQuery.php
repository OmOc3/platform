<?php

namespace App\Modules\Students\Queries;

use App\Modules\Students\Models\MistakeItem;
use App\Modules\Students\Models\Student;
use Illuminate\Support\Collection;

class MistakeGroupsQuery
{
    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function forStudent(Student $student): Collection
    {
        return MistakeItem::query()
            ->with('lecture')
            ->where('student_id', $student->id)
            ->orderByDesc('created_at')
            ->get()
            ->groupBy('lecture_id')
            ->map(function ($items): array {
                $first = $items->first();

                return [
                    'lecture' => $first?->lecture,
                    'count' => $items->count(),
                    'latest_at' => $items->max('created_at'),
                    'score_lost' => $items->sum('score_lost'),
                ];
            })
            ->sortByDesc('latest_at')
            ->values();
    }
}
