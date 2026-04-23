<?php

namespace App\Modules\Support\Actions;

use App\Modules\Students\Models\Student;
use App\Modules\Support\Models\SupportTicket;
use App\Shared\Contracts\AuditLogger;
use App\Shared\Contracts\TicketAssignmentService;
use App\Shared\Enums\TicketStatus;
use Illuminate\Support\Facades\DB;

class CreateSupportTicketAction
{
    public function __construct(
        private readonly TicketAssignmentService $ticketAssignmentService,
        private readonly AuditLogger $auditLogger,
    ) {
    }

    public function execute(Student $student, array $data): SupportTicket
    {
        return DB::transaction(function () use ($student, $data): SupportTicket {
            $ticket = SupportTicket::query()->create([
                'student_id' => $student->id,
                'support_ticket_type_id' => $data['support_ticket_type_id'],
                'subject' => $data['subject'],
                'status' => TicketStatus::Open,
                'last_activity_at' => now(),
            ]);

            $ticket->replies()->create([
                'author_type' => $student->getMorphClass(),
                'author_id' => $student->id,
                'body' => $data['body'],
                'is_staff_reply' => false,
            ]);

            $assignedTicket = $this->ticketAssignmentService->assign([
                'ticket' => $ticket,
            ]);

            if ($assignedTicket instanceof SupportTicket) {
                $ticket = $assignedTicket;
            }

            $ticket->refresh();

            if ($ticket->assigned_admin_id !== null && $ticket->status === TicketStatus::Open) {
                $ticket->update([
                    'status' => TicketStatus::Assigned,
                ]);
            }

            $this->auditLogger->log(
                event: 'support.ticket.created',
                actor: $student,
                subject: $ticket,
                newValues: $ticket->fresh(['type.defaultTeam', 'team', 'assignedAdmin', 'replies.author'])->toArray(),
            );

            return $ticket->fresh(['type.defaultTeam', 'team', 'assignedAdmin', 'firstReply', 'latestReply']);
        });
    }
}
