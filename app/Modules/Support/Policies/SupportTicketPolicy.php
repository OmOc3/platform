<?php

namespace App\Modules\Support\Policies;

use App\Modules\Identity\Models\Admin;
use App\Modules\Students\Models\Student;
use App\Modules\Support\Models\SupportTicket;

class SupportTicketPolicy
{
    public function viewAny(Admin|Student $user): bool
    {
        return $user instanceof Admin ? $user->can('tickets.view') : true;
    }

    public function create(Student $student): bool
    {
        return true;
    }

    public function view(Admin|Student $user, SupportTicket $ticket): bool
    {
        if ($user instanceof Admin) {
            return $user->can('tickets.view');
        }

        return $ticket->student_id === $user->id;
    }

    public function reply(Admin|Student $user, SupportTicket $ticket): bool
    {
        if ($user instanceof Admin) {
            return $user->can('tickets.manage') && $ticket->status->allowsAdminReply();
        }

        return $ticket->student_id === $user->id && $ticket->status->allowsStudentReply();
    }

    public function update(Admin $admin, SupportTicket $ticket): bool
    {
        return $admin->can('tickets.manage');
    }

    public function assign(Admin $admin, SupportTicket $ticket): bool
    {
        return $admin->can('tickets.manage');
    }
}
