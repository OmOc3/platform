<?php

namespace App\Modules\Support\Queries;

use App\Modules\Students\Models\Student;
use App\Modules\Support\Models\SupportTicket;
use Illuminate\Database\Eloquent\Builder;

class StudentSupportTicketsQuery
{
    public function builder(Student $student): Builder
    {
        return SupportTicket::query()
            ->with(['type.defaultTeam', 'team', 'assignedAdmin', 'latestReply.author'])
            ->withCount('replies')
            ->where('student_id', $student->id)
            ->orderByDesc('last_activity_at')
            ->latest('created_at');
    }
}
