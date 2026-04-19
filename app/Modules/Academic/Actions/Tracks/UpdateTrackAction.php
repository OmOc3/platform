<?php

namespace App\Modules\Academic\Actions\Tracks;

use App\Modules\Academic\Models\Track;
use App\Shared\Contracts\AuditLogger;

class UpdateTrackAction
{
    public function __construct(private readonly AuditLogger $auditLogger)
    {
    }

    public function execute(Track $track, array $data, mixed $actor): Track
    {
        $oldValues = $track->toArray();

        $track->update($data);

        $this->auditLogger->log(
            event: 'academic.track.updated',
            actor: $actor,
            subject: $track,
            oldValues: $oldValues,
            newValues: $track->fresh()->toArray(),
        );

        return $track->refresh();
    }
}
