<?php

namespace App\Shared\Services;

use App\Modules\Identity\Models\Admin;
use App\Modules\Support\Models\SupportTeam;
use App\Modules\Support\Models\SupportTicket;
use App\Shared\Contracts\TicketAssignmentService;
use App\Shared\Enums\TicketStatus;
use Illuminate\Database\Eloquent\Builder;
use InvalidArgumentException;

class DatabaseTicketAssignmentService implements TicketAssignmentService
{
    public function assign(array $payload): mixed
    {
        $ticket = $payload['ticket'] ?? null;

        if (! $ticket instanceof SupportTicket) {
            throw new InvalidArgumentException('The ticket payload is required for assignment.');
        }

        $ticket->loadMissing('type.defaultTeam');

        $team = $payload['team'] ?? null;

        if (is_numeric($payload['team_id'] ?? null)) {
            $team = SupportTeam::query()->find($payload['team_id']);
        }

        if (! $team instanceof SupportTeam) {
            $team = $ticket->type?->defaultTeam;
        }

        $assignedAdmin = null;

        if ($team instanceof SupportTeam) {
            $assignedAdmin = $team->admins()
                ->where('admins.is_active', true)
                ->withCount([
                    'assignedSupportTickets as active_tickets_count' => fn (Builder $query) => $query->whereIn('status', TicketStatus::activeWorkloadValues()),
                ])
                ->get()
                ->filter(fn (Admin $admin): bool => $admin->can('tickets.manage'))
                ->sortBy(fn (Admin $admin): string => sprintf('%08d-%08d', $admin->active_tickets_count, $admin->id))
                ->first();
        }

        $ticket->update([
            'support_team_id' => $team?->id,
            'assigned_admin_id' => $assignedAdmin?->id,
        ]);

        return $ticket->fresh(['type.defaultTeam', 'team', 'assignedAdmin']);
    }
}
