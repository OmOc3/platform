<?php

namespace App\Shared\Services;

use App\Shared\Contracts\TicketAssignmentService;
use BadMethodCallException;

class DatabaseTicketAssignmentService implements TicketAssignmentService
{
    public function assign(array $payload): mixed
    {
        throw new BadMethodCallException('Ticket assignment is not implemented in Milestone 1.');
    }
}
