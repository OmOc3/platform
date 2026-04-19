<?php

namespace App\Modules\Students\Queries;

use App\Modules\Students\Models\MistakeItem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class AdminMistakesIndexQuery
{
    public function builder(Request $request): Builder
    {
        $search = $request->string('search')->toString();

        return MistakeItem::query()
            ->with(['student', 'lecture', 'exam'])
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $builder) use ($search): void {
                    $builder->where('question_text', 'like', "%{$search}%")
                        ->orWhereHas('student', fn ($studentQuery) => $studentQuery->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('lecture', fn ($lectureQuery) => $lectureQuery->where('title', 'like', "%{$search}%"));
                });
            })
            ->latest('created_at');
    }
}
