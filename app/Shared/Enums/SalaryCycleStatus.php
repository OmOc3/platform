<?php

namespace App\Shared\Enums;

enum SalaryCycleStatus: string
{
    case Draft = 'draft';
    case PendingApproval = 'pending_approval';
    case Finalized = 'finalized';
    case Paid = 'paid';
}
