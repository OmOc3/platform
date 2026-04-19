<?php

namespace App\Modules\Academic\Actions\Exams;

use App\Modules\Academic\Models\Exam;
use App\Shared\Contracts\AuditLogger;
use Illuminate\Support\Facades\DB;

class SaveExamAction
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
        private readonly SyncExamQuestionsAction $syncExamQuestionsAction,
    ) {
    }

    public function execute(array $data, mixed $actor, ?Exam $exam = null): Exam
    {
        return DB::transaction(function () use ($data, $actor, $exam): Exam {
            $exam ??= new Exam();
            $exam->loadMissing('examQuestions.question.choices');

            $oldValues = $exam->exists ? $exam->toArray() : [];
            $questions = $data['questions'] ?? [];
            $metadata = array_filter([
                ...($exam->metadata ?? []),
                ...($data['metadata'] ?? []),
            ], fn (mixed $value): bool => $value !== null && $value !== '');

            unset($data['questions'], $data['metadata']);

            $exam->fill([
                ...$data,
                'price_amount' => $data['is_free'] ? 0 : $data['price_amount'],
                'metadata' => $metadata === [] ? null : $metadata,
            ]);
            $exam->save();

            $this->syncExamQuestionsAction->execute($exam, $questions);

            $freshExam = $exam->fresh(['examQuestions.question.choices']);

            $this->auditLogger->log(
                event: $exam->wasRecentlyCreated ? 'academic.exam.created' : 'academic.exam.updated',
                actor: $actor,
                subject: $freshExam,
                oldValues: $oldValues,
                newValues: $freshExam->toArray(),
            );

            return $freshExam;
        });
    }
}
