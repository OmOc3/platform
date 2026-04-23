<?php

namespace App\Modules\Support\Actions;

use App\Modules\Identity\Models\Admin;
use App\Modules\Students\Models\Student;
use App\Modules\Support\Models\SupportTicket;
use App\Shared\Contracts\AuditLogger;
use App\Shared\Enums\TicketStatus;
use Illuminate\Support\Facades\DB;

class ReplyToSupportTicketAction
{
    public function __construct(private readonly AuditLogger $auditLogger)
    {
    }

    public function execute(SupportTicket $ticket, Student|Admin $author, array $data): SupportTicket
    {
        return DB::transaction(function () use ($ticket, $author, $data): SupportTicket {
            $ticket->replies()->create([
                'author_type' => $author->getMorphClass(),
                'author_id' => $author->getKey(),
                'body' => $data['body'],
                'is_staff_reply' => $author instanceof Admin,
            ]);

            $nextStatus = match (true) {
                $author instanceof Admin && $ticket->status === TicketStatus::Resolved => TicketStatus::Resolved,
                $author instanceof Admin => TicketStatus::WaitingCustomer,
                default => TicketStatus::WaitingInternal,
            };

            $ticket->update([
                'status' => $nextStatus,
                'last_activity_at' => now(),
                'resolved_at' => $nextStatus === TicketStatus::Resolved ? $ticket->resolved_at : null,
                'closed_at' => null,
            ]);

            $this->auditLogger->log(
                event: $author instanceof Admin ? 'support.ticket.staff-replied' : 'support.ticket.student-replied',
                actor: $author,
                subject: $ticket,
                newValues: $ticket->fresh(['replies.author', 'type', 'team', 'assignedAdmin'])->toArray(),
            );

            return $ticket->fresh(['replies.author', 'type', 'team', 'assignedAdmin']);
        });
    }
}
