<?php

namespace App\Modules\Support\Queries;

use App\Modules\Support\Models\SupportTicket;
use App\Shared\Enums\TicketStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class AdminSupportTicketsIndexQuery
{
    public function builder(Request $request): Builder
    {
        $search = $request->string('search')->toString();
        $status = $request->string('status')->toString();
        $typeId = $request->integer('type');
        $teamId = $request->integer('team');
        $assignment = $request->string('assignment')->toString();
        $adminId = auth('admin')->id();

        return SupportTicket::query()
            ->with([
                'student.ownerAdmin',
                'student.center',
                'student.group',
                'type.defaultTeam',
                'team',
                'assignedAdmin',
                'latestReply.author',
            ])
            ->withCount('replies')
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $builder) use ($search): void {
                    $builder->where('subject', 'like', "%{$search}%")
                        ->orWhereHas('replies', function (Builder $replyQuery) use ($search): void {
                            $replyQuery->where('body', 'like', "%{$search}%");
                        })
                        ->orWhereHas('student', function (Builder $studentQuery) use ($search): void {
                            $studentQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('student_number', 'like', "%{$search}%")
                                ->orWhere('phone', 'like', "%{$search}%");
                        });
                });
            })
            ->when($status !== '', fn (Builder $query) => $query->where('status', $status))
            ->when($typeId > 0, fn (Builder $query) => $query->where('support_ticket_type_id', $typeId))
            ->when($teamId > 0, fn (Builder $query) => $query->where('support_team_id', $teamId))
            ->when($assignment === 'mine' && $adminId, fn (Builder $query) => $query->where('assigned_admin_id', $adminId))
            ->when($assignment === 'unassigned', fn (Builder $query) => $query->whereNull('assigned_admin_id'))
            ->orderByRaw(sprintf(
                "case when status in ('%s') then 0 else 1 end",
                implode("','", TicketStatus::activeWorkloadValues())
            ))
            ->orderByDesc('last_activity_at')
            ->latest('created_at');
    }
}
