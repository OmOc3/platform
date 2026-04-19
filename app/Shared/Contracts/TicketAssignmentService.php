<?php

namespace App\Shared\Contracts;

interface TicketAssignmentService
{
    public function assign(array $payload): mixed;
}
