<?php

namespace App\Modules\Support\Policies;

use App\Modules\Identity\Models\Admin;
use App\Modules\Students\Models\Student;
use App\Modules\Support\Models\ForumThread;

class ForumThreadPolicy
{
    public function viewAny(Admin|Student $user): bool
    {
        return $user instanceof Admin ? $user->can('forum.view') : true;
    }

    public function view(Admin|Student $user, ForumThread $thread): bool
    {
        if ($user instanceof Admin) {
            return $user->can('forum.view');
        }

        return $thread->visibility->value === 'public' || $thread->student_id === $user->id;
    }

    public function create(Student $student): bool
    {
        return true;
    }

    public function reply(Admin|Student $user, ForumThread $thread): bool
    {
        if ($user instanceof Admin) {
            return $user->can('forum.reply');
        }

        return $thread->status->value !== 'closed';
    }

    public function update(Admin $admin, ForumThread $thread): bool
    {
        return $admin->can('forum.manage');
    }
}
