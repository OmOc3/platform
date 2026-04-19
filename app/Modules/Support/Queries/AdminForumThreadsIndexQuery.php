<?php

namespace App\Modules\Support\Queries;

use App\Modules\Support\Models\ForumThread;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class AdminForumThreadsIndexQuery
{
    public function builder(Request $request): Builder
    {
        $search = $request->string('search')->toString();
        $status = $request->string('status')->toString();
        $visibility = $request->string('visibility')->toString();

        return ForumThread::query()
            ->with(['student', 'firstMessage', 'latestMessage'])
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $builder) use ($search): void {
                    $builder->where('title', 'like', "%{$search}%")
                        ->orWhereHas('student', fn ($studentQuery) => $studentQuery->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($status !== '', fn (Builder $query) => $query->where('status', $status))
            ->when($visibility !== '', fn (Builder $query) => $query->where('visibility', $visibility))
            ->orderByDesc('last_activity_at');
    }
}
