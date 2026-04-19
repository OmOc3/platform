<?php

namespace App\Modules\Students\Policies;

use App\Modules\Identity\Models\Admin;
use App\Modules\Students\Models\MistakeItem;
use App\Modules\Students\Models\Student;

class MistakeItemPolicy
{
    public function viewAny(Admin|Student $user): bool
    {
        return $user instanceof Admin ? $user->can('mistakes.view') : true;
    }

    public function view(Admin|Student $user, MistakeItem $mistakeItem): bool
    {
        if ($user instanceof Admin) {
            return $user->can('mistakes.view');
        }

        return $mistakeItem->student_id === $user->id;
    }
}
