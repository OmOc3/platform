<?php

namespace App\Modules\Academic\Actions\Exams;

use App\Modules\Academic\Models\Exam;
use App\Modules\Academic\Models\ExamQuestion;
use App\Modules\Academic\Models\Question;
use App\Modules\Academic\Models\QuestionChoice;
use App\Shared\Enums\QuestionType;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class SyncExamQuestionsAction
{
    /**
     * @param  array<int, array<string, mixed>>  $questions
     */
    public function execute(Exam $exam, array $questions): void
    {
        $exam->loadMissing('examQuestions.question.choices');

        /** @var Collection<int, ExamQuestion> $existingExamQuestions */
        $existingExamQuestions = $exam->examQuestions->keyBy('question_id');
        $keptQuestionIds = [];

        foreach (array_values($questions) as $questionIndex => $payload) {
            $question = $this->resolveQuestion($existingExamQuestions, $payload);

            $question->fill([
                'type' => QuestionType::MultipleChoice,
                'prompt' => $payload['prompt'],
                'explanation' => $payload['explanation'] ?: null,
                'is_active' => true,
                'metadata' => $question->metadata,
            ]);
            $question->save();

            $this->syncChoices($question, $payload['choices']);

            $exam->examQuestions()->updateOrCreate(
                ['question_id' => $question->id],
                [
                    'sort_order' => $questionIndex + 1,
                    'max_score' => $payload['max_score'],
                ],
            );

            $keptQuestionIds[] = $question->id;
        }

        $deleteQuery = $exam->examQuestions();

        if ($keptQuestionIds !== []) {
            $deleteQuery->whereNotIn('question_id', $keptQuestionIds);
        }

        $deleteQuery->delete();

        $exam->forceFill([
            'question_count' => count($keptQuestionIds),
        ])->saveQuietly();
    }

    /**
     * @param  Collection<int, ExamQuestion>  $existingExamQuestions
     * @param  array<string, mixed>  $payload
     */
    private function resolveQuestion(Collection $existingExamQuestions, array $payload): Question
    {
        $questionId = $payload['question_id'] ?? null;

        if ($questionId === null) {
            return new Question();
        }

        $examQuestion = $existingExamQuestions->get((int) $questionId);

        if (! $examQuestion instanceof ExamQuestion) {
            throw ValidationException::withMessages([
                'questions' => ['تعذر مزامنة سؤال غير مرتبط بهذا الاختبار.'],
            ]);
        }

        return $examQuestion->question;
    }

    /**
     * @param  array<int, array<string, mixed>>  $choices
     */
    private function syncChoices(Question $question, array $choices): void
    {
        $question->loadMissing('choices');

        /** @var Collection<int, QuestionChoice> $existingChoices */
        $existingChoices = $question->choices->keyBy('id');
        $keptChoiceIds = [];

        foreach (array_values($choices) as $choiceIndex => $payload) {
            $choiceId = $payload['choice_id'] ?? null;

            if ($choiceId !== null) {
                $choice = $existingChoices->get((int) $choiceId);

                if (! $choice instanceof QuestionChoice) {
                    throw ValidationException::withMessages([
                        'questions' => ['تعذر مزامنة اختيار غير مرتبط بهذا السؤال.'],
                    ]);
                }
            } else {
                $choice = new QuestionChoice();
                $choice->question()->associate($question);
            }

            $choice->fill([
                'content' => $payload['content'],
                'is_correct' => $payload['is_correct'],
                'sort_order' => $choiceIndex + 1,
                'is_active' => true,
                'metadata' => $choice->metadata,
            ]);
            $choice->save();

            $keptChoiceIds[] = $choice->id;
        }

        if ($keptChoiceIds === []) {
            $question->choices()->update(['is_active' => false]);

            return;
        }

        $question->choices()
            ->whereNotIn('id', $keptChoiceIds)
            ->update(['is_active' => false]);
    }
}
