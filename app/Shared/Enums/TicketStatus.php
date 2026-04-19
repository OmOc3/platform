<?php

namespace App\Shared\Enums;

enum TicketStatus: string
{
    case Open = 'open';
    case Assigned = 'assigned';
    case WaitingCustomer = 'waiting_customer';
    case WaitingInternal = 'waiting_internal';
    case Resolved = 'resolved';
    case Closed = 'closed';
}
