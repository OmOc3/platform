<?php

namespace App\Modules\Support\Actions;

use App\Modules\Support\Models\ForumThread;
use App\Shared\Contracts\AuditLogger;

class UpdateForumThreadStatusAction
{
    public function __construct(private readonly AuditLogger $auditLogger)
    {
    }

    public function execute(ForumThread $thread, array $data, mixed $actor): ForumThread
    {
        $oldValues = $thread->toArray();

        $thread->update([
            'status' => $data['status'],
            'visibility' => $data['visibility'],
            'closed_at' => $data['status'] === 'closed' ? now() : null,
        ]);

        $this->auditLogger->log(
            event: 'support.forum-thread.status-updated',
            actor: $actor,
            subject: $thread,
            oldValues: $oldValues,
            newValues: $thread->fresh()->toArray(),
        );

        return $thread;
    }
}
