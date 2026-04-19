<?php

namespace App\Modules\Academic\Actions\Tracks;

use App\Modules\Academic\Models\Track;
use App\Shared\Contracts\AuditLogger;

class CreateTrackAction
{
    public function __construct(private readonly AuditLogger $auditLogger)
    {
    }

    public function execute(array $data, mixed $actor): Track
    {
        $track = Track::query()->create($data);

        $this->auditLogger->log(
            event: 'academic.track.created',
            actor: $actor,
            subject: $track,
            newValues: $track->fresh()->toArray(),
        );

        return $track;
    }
}
