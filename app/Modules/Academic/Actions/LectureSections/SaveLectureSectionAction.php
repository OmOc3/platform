<?php

namespace App\Modules\Academic\Actions\LectureSections;

use App\Modules\Academic\Models\LectureSection;
use App\Shared\Contracts\AuditLogger;

class SaveLectureSectionAction
{
    public function __construct(private readonly AuditLogger $auditLogger)
    {
    }

    public function execute(array $data, mixed $actor, ?LectureSection $section = null): LectureSection
    {
        $section ??= new LectureSection();
        $oldValues = $section->exists ? $section->toArray() : [];

        $section->fill($data);
        $section->save();

        $this->auditLogger->log(
            event: $section->wasRecentlyCreated ? 'academic.lecture-section.created' : 'academic.lecture-section.updated',
            actor: $actor,
            subject: $section,
            oldValues: $oldValues,
            newValues: $section->fresh()->toArray(),
        );

        return $section;
    }
}
