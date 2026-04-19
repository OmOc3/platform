<?php

namespace App\Modules\Academic\Actions\Exams;

use App\Modules\Academic\Models\Exam;
use App\Shared\Contracts\AuditLogger;

class SaveExamAction
{
    public function __construct(private readonly AuditLogger $auditLogger)
    {
    }

    public function execute(array $data, mixed $actor, ?Exam $exam = null): Exam
    {
        $exam ??= new Exam();
        $oldValues = $exam->exists ? $exam->toArray() : [];

        $exam->fill([
            ...$data,
            'price_amount' => $data['is_free'] ? 0 : $data['price_amount'],
        ]);
        $exam->save();

        $this->auditLogger->log(
            event: $exam->wasRecentlyCreated ? 'academic.exam.created' : 'academic.exam.updated',
            actor: $actor,
            subject: $exam,
            oldValues: $oldValues,
            newValues: $exam->fresh()->toArray(),
        );

        return $exam;
    }
}
