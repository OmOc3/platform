<?php

namespace App\Modules\Academic\Actions\CurriculumSections;

use App\Modules\Academic\Models\CurriculumSection;
use App\Shared\Contracts\AuditLogger;

class SaveCurriculumSectionAction
{
    public function __construct(private readonly AuditLogger $auditLogger)
    {
    }

    public function execute(array $data, mixed $actor, ?CurriculumSection $section = null): CurriculumSection
    {
        $section ??= new CurriculumSection();
        $oldValues = $section->exists ? $section->toArray() : [];

        $section->fill($data);
        $section->save();

        $this->auditLogger->log(
            event: $section->wasRecentlyCreated ? 'academic.curriculum-section.created' : 'academic.curriculum-section.updated',
            actor: $actor,
            subject: $section,
            oldValues: $oldValues,
            newValues: $section->fresh()->toArray(),
        );

        return $section;
    }
}
