<?php

namespace App\Shared\Services;

use App\Modules\Identity\Models\AuditLog;
use App\Shared\Contracts\AuditLogger;
use Illuminate\Database\Eloquent\Model;

class DatabaseAuditLogger implements AuditLogger
{
    public function log(
        string $event,
        mixed $actor = null,
        mixed $subject = null,
        array $oldValues = [],
        array $newValues = [],
        array $meta = [],
    ): void {
        AuditLog::query()->create([
            'event' => $event,
            'actor_type' => $actor instanceof Model ? $actor->getMorphClass() : null,
            'actor_id' => $actor instanceof Model ? $actor->getKey() : null,
            'auditable_type' => $subject instanceof Model ? $subject->getMorphClass() : null,
            'auditable_id' => $subject instanceof Model ? $subject->getKey() : null,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'meta' => $meta,
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
            'created_at' => now(),
        ]);
    }
}
