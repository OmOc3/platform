<?php

namespace App\Modules\Support\Actions;

use App\Modules\Identity\Models\Admin;
use App\Modules\Support\Models\SupportTicket;
use App\Shared\Contracts\AuditLogger;
use App\Shared\Contracts\TicketAssignmentService;
use App\Shared\Enums\TicketStatus;

class AssignSupportTicketAction
{
    public function __construct(
        private readonly TicketAssignmentService $ticketAssignmentService,
        private readonly AuditLogger $auditLogger,
    ) {
    }

    public function execute(SupportTicket $ticket, array $data, Admin $actor): SupportTicket
    {
        $oldValues = $ticket->toArray();
        $supportTeamId = $data['support_team_id'] ?? null;
        $assignedAdminId = $data['assigned_admin_id'] ?? null;

        $supportTeamId = $supportTeamId ?: null;
        $assignedAdminId = $assignedAdminId ?: null;

        $ticket->update([
            'support_team_id' => $supportTeamId,
            'assigned_admin_id' => $assignedAdminId,
            'status' => $assignedAdminId && $ticket->status === TicketStatus::Open
                ? TicketStatus::Assigned
                : ($assignedAdminId === null && $ticket->status === TicketStatus::Assigned
                    ? TicketStatus::Open
                    : $ticket->status),
        ]);

        $this->auditLogger->log(
            event: 'support.ticket.assignment-updated',
            actor: $actor,
            subject: $ticket,
            oldValues: $oldValues,
            newValues: $ticket->fresh(['team', 'assignedAdmin'])->toArray(),
        );

        return $ticket->fresh(['team', 'assignedAdmin']);
    }

    public function autoAssign(SupportTicket $ticket, Admin $actor): SupportTicket
    {
        $oldValues = $ticket->toArray();
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
            event: 'support.ticket.assignment-auto-updated',
            actor: $actor,
            subject: $ticket,
            oldValues: $oldValues,
            newValues: $ticket->fresh(['team', 'assignedAdmin'])->toArray(),
        );

        return $ticket->fresh(['team', 'assignedAdmin']);
    }
}
