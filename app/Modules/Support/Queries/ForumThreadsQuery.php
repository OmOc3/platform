<?php

namespace App\Modules\Support\Queries;

use App\Modules\Students\Models\Student;
use App\Modules\Support\Models\ForumThread;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class ForumThreadsQuery
{
    public function paginate(Request $request, Student $student, bool $onlyMine = false): LengthAwarePaginator
    {
        $search = $request->string('search')->toString();

        return ForumThread::query()
            ->with(['student', 'firstMessage.attachments', 'latestMessage', 'messages'])
            ->when($onlyMine, fn ($query) => $query->where('student_id', $student->id))
            ->unless($onlyMine, fn ($query) => $query->where('visibility', 'public'))
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($builder) use ($search): void {
                    $builder->where('title', 'like', "%{$search}%")
                        ->orWhereHas('firstMessage', fn ($messageQuery) => $messageQuery->where('body', 'like', "%{$search}%"));
                });
            })
            ->orderByDesc('last_activity_at')
            ->paginate(12)
            ->withQueryString();
    }
}
