<?php

namespace App\Modules\Support\Actions;

use App\Modules\Identity\Models\Admin;
use App\Modules\Support\Models\SupportTicket;
use App\Shared\Contracts\AuditLogger;
use App\Shared\Enums\TicketStatus;

class UpdateSupportTicketStatusAction
{
    public function __construct(private readonly AuditLogger $auditLogger)
    {
    }

    public function execute(SupportTicket $ticket, array $data, Admin $actor): SupportTicket
    {
        $oldValues = $ticket->toArray();
        $status = TicketStatus::from($data['status']);

        $ticket->update([
            'status' => $status,
            'resolved_at' => $status === TicketStatus::Resolved
                ? ($ticket->resolved_at ?? now())
                : ($status === TicketStatus::Closed ? $ticket->resolved_at : null),
            'closed_at' => $status === TicketStatus::Closed
                ? ($ticket->closed_at ?? now())
                : null,
        ]);

        $this->auditLogger->log(
            event: 'support.ticket.status-updated',
            actor: $actor,
            subject: $ticket,
            oldValues: $oldValues,
            newValues: $ticket->fresh()->toArray(),
        );

        return $ticket;
    }
}
