<?php

namespace App\Shared\Services;

use App\Modules\Academic\Models\Exam;
use App\Modules\Academic\Models\ExamAttempt;
use App\Modules\Academic\Models\ExamAttemptAnswer;
use App\Modules\Academic\Models\ExamQuestion;
use App\Modules\Academic\Models\QuestionChoice;
use App\Modules\Students\Actions\Mistakes\SyncExamAttemptMistakesAction;
use App\Modules\Students\Models\Student;
use App\Shared\Contracts\AccessResolver;
use App\Shared\Contracts\AuditLogger;
use App\Shared\Contracts\ExamAttemptService;
use App\Shared\Enums\ContentAccessState;
use App\Shared\Enums\ExamAttemptStatus;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DatabaseExamAttemptService implements ExamAttemptService
{
    public function __construct(
        private readonly AccessResolver $accessResolver,
        private readonly AuditLogger $auditLogger,
        private readonly SyncExamAttemptMistakesAction $syncExamAttemptMistakesAction,
    ) {
    }

    public function start(array $payload): mixed
    {
        /** @var Student $student */
        $student = $payload['student'];
        /** @var Exam $exam */
        $exam = $payload['exam'];

        $exam->loadMissing('examQuestions.question.choices');

        $this->assertExamCanStart($student, $exam);

        $existingAttempt = ExamAttempt::query()
            ->where('student_id', $student->id)
            ->where('exam_id', $exam->id)
            ->where('status', ExamAttemptStatus::InProgress)
            ->latest('started_at')
            ->first();

        if ($existingAttempt instanceof ExamAttempt) {
            if ($this->isExpired($existingAttempt)) {
                $this->submit([
                    'attempt' => $existingAttempt,
                    'student' => $student,
                    'submitted_by_timer' => true,
                ]);
            } else {
                return $existingAttempt->fresh(['exam.examQuestions.question.choices', 'answers']);
            }
        }

        $maxAttempts = $this->maxAttempts($exam);
        $attemptsCount = ExamAttempt::query()
            ->where('student_id', $student->id)
            ->where('exam_id', $exam->id)
            ->count();

        if ($attemptsCount >= $maxAttempts) {
            throw ValidationException::withMessages([
                'exam' => ['تم استهلاك عدد المحاولات المتاح لهذا الاختبار.'],
            ]);
        }

        $attempt = ExamAttempt::query()->create([
            'exam_id' => $exam->id,
            'student_id' => $student->id,
            'status' => ExamAttemptStatus::InProgress,
            'started_at' => now(),
            'total_questions' => $exam->examQuestions->count(),
            'answered_questions' => 0,
            'max_score' => (int) $exam->examQuestions->sum('max_score'),
            'attempt_number' => $attemptsCount + 1,
            'time_limit_snapshot' => $exam->duration_minutes,
            'result_meta' => null,
        ]);

        $this->auditLogger->log(
            event: 'academic.exam-attempt.started',
            actor: $student,
            subject: $attempt,
            newValues: $attempt->fresh()->toArray(),
            meta: [
                'exam_id' => $exam->id,
                'attempt_number' => $attempt->attempt_number,
            ],
        );

        return $attempt->fresh(['exam.examQuestions.question.choices', 'answers']);
    }

    public function saveProgress(array $payload): mixed
    {
        /** @var ExamAttempt $attempt */
        $attempt = $payload['attempt'];
        /** @var Student $student */
        $student = $payload['student'];
        $answers = (array) ($payload['answers'] ?? []);

        $attempt->loadMissing('exam.examQuestions.question.choices');

        if ($attempt->student_id !== $student->id) {
            throw ValidationException::withMessages([
                'attempt' => ['لا يمكنك تعديل محاولة لا تخص حسابك.'],
            ]);
        }

        if ($attempt->status !== ExamAttemptStatus::InProgress) {
            return $attempt->fresh(['exam.examQuestions.question.choices', 'answers']);
        }

        if ($this->isExpired($attempt)) {
            return $this->submit([
                'attempt' => $attempt,
                'student' => $student,
                'answers' => $answers,
                'submitted_by_timer' => true,
            ]);
        }

        return DB::transaction(function () use ($attempt, $answers): ExamAttempt {
            $lockedAttempt = ExamAttempt::query()
                ->with(['exam.examQuestions.question.choices', 'answers'])
                ->lockForUpdate()
                ->findOrFail($attempt->id);

            $this->syncAnswers($lockedAttempt, $answers);

            $lockedAttempt->answered_questions = $lockedAttempt->answers()->whereNotNull('selected_answer')->count();
            $lockedAttempt->save();

            return $lockedAttempt->fresh(['exam.examQuestions.question.choices', 'answers']);
        });
    }

    public function submit(array $payload): mixed
    {
        /** @var ExamAttempt $attempt */
        $attempt = $payload['attempt'];
        /** @var Student $student */
        $student = $payload['student'];
        $answers = (array) ($payload['answers'] ?? []);
        $submittedByTimer = (bool) ($payload['submitted_by_timer'] ?? false);

        return DB::transaction(function () use ($attempt, $student, $answers, $submittedByTimer): ExamAttempt {
            /** @var ExamAttempt $lockedAttempt */
            $lockedAttempt = ExamAttempt::query()
                ->with(['exam.examQuestions.question.choices', 'answers.question.choices'])
                ->lockForUpdate()
                ->findOrFail($attempt->id);

            if ($lockedAttempt->student_id !== $student->id) {
                throw ValidationException::withMessages([
                    'attempt' => ['لا يمكنك إرسال محاولة لا تخص حسابك.'],
                ]);
            }

            if ($lockedAttempt->status === ExamAttemptStatus::Graded) {
                return $lockedAttempt->fresh(['exam.examQuestions.question.choices', 'answers.question.choices']);
            }

            $this->syncAnswers($lockedAttempt, $answers);

            $grading = $this->gradeAttempt($lockedAttempt);

            $oldValues = $lockedAttempt->toArray();

            $lockedAttempt->fill([
                'status' => ExamAttemptStatus::Graded,
                'submitted_at' => $lockedAttempt->submitted_at ?? now(),
                'graded_at' => now(),
                'total_questions' => $grading['total_questions'],
                'answered_questions' => $grading['answered_questions'],
                'correct_answers_count' => $grading['correct_answers_count'],
                'total_score' => $grading['total_score'],
                'max_score' => $grading['max_score'],
                'result_meta' => [
                    'wrong_count' => $grading['wrong_count'],
                    'skipped_count' => $grading['skipped_count'],
                    'submitted_by_timer' => $submittedByTimer,
                ],
            ]);
            $lockedAttempt->save();

            $this->syncExamAttemptMistakesAction->execute($lockedAttempt->fresh(['exam.lecture', 'answers.question.choices']));

            $freshAttempt = $lockedAttempt->fresh(['exam.examQuestions.question.choices', 'answers.question.choices']);

            $this->auditLogger->log(
                event: 'academic.exam-attempt.graded',
                actor: $student,
                subject: $freshAttempt,
                oldValues: $oldValues,
                newValues: $freshAttempt->toArray(),
                meta: [
                    'submitted_by_timer' => $submittedByTimer,
                    'total_score' => $freshAttempt->total_score,
                    'max_score' => $freshAttempt->max_score,
                ],
            );

            return $freshAttempt;
        });
    }

    private function assertExamCanStart(Student $student, Exam $exam): void
    {
        if (! $exam->is_active || ($exam->published_at && $exam->published_at->isFuture())) {
            throw ValidationException::withMessages([
                'exam' => ['هذا الاختبار غير متاح حاليًا.'],
            ]);
        }

        if ($exam->examQuestions->isEmpty()) {
            throw ValidationException::withMessages([
                'exam' => ['لا يمكن بدء اختبار بدون أسئلة منشورة.'],
            ]);
        }

        $access = $this->accessResolver->resolveState($student, $exam);

        if (! in_array($access['state'], [
            ContentAccessState::Open,
            ContentAccessState::Free,
            ContentAccessState::OwnedViaEntitlement,
        ], true)) {
            throw ValidationException::withMessages([
                'exam' => ['لا تملك صلاحية الدخول إلى هذا الاختبار بعد.'],
            ]);
        }
    }

    private function maxAttempts(Exam $exam): int
    {
        return max(1, (int) data_get($exam->metadata, 'max_attempts', 1));
    }

    private function isExpired(ExamAttempt $attempt): bool
    {
        $expiresAt = $attempt->expiresAt();

        return $expiresAt instanceof Carbon && now()->greaterThanOrEqualTo($expiresAt);
    }

    /**
     * @param  array<int|string, mixed>  $answers
     */
    private function syncAnswers(ExamAttempt $attempt, array $answers): void
    {
        $attempt->loadMissing('exam.examQuestions.question.choices', 'answers');

        /** @var Collection<int, ExamQuestion> $examQuestions */
        $examQuestions = $attempt->exam->examQuestions->keyBy('question_id');

        foreach ($answers as $questionId => $selectedChoiceId) {
            $questionId = (int) $questionId;
            $selectedChoiceId = filled($selectedChoiceId) ? (int) $selectedChoiceId : null;
            $examQuestion = $examQuestions->get($questionId);

            if (! $examQuestion instanceof ExamQuestion) {
                continue;
            }

            if ($selectedChoiceId === null) {
                $attempt->answers()->where('question_id', $questionId)->delete();

                continue;
            }

            $choice = $examQuestion->question?->choices
                ?->first(fn (QuestionChoice $choice): bool => $choice->id === $selectedChoiceId && $choice->is_active);

            if (! $choice instanceof QuestionChoice) {
                throw ValidationException::withMessages([
                    'answers' => ['تم إرسال اختيار غير صالح لهذا الاختبار.'],
                ]);
            }

            /** @var ExamAttemptAnswer $answer */
            $answer = ExamAttemptAnswer::query()->firstOrNew([
                'exam_attempt_id' => $attempt->id,
                'question_id' => $questionId,
            ]);

            $answer->fill([
                'selected_answer' => (string) $choice->id,
                'answer_payload' => null,
                'max_score' => $examQuestion->max_score,
            ]);
            $answer->save();
        }

        $attempt->unsetRelation('answers');
    }

    /**
     * @return array{
     *     total_questions:int,
     *     answered_questions:int,
     *     correct_answers_count:int,
     *     wrong_count:int,
     *     skipped_count:int,
     *     total_score:int,
     *     max_score:int
     * }
     */
    private function gradeAttempt(ExamAttempt $attempt): array
    {
        $attempt->loadMissing('exam.examQuestions.question.choices', 'answers.question.choices');

        /** @var Collection<int, ExamAttemptAnswer> $answers */
        $answers = $attempt->answers->keyBy('question_id');

        $totalQuestions = $attempt->exam->examQuestions->count();
        $maxScore = 0;
        $answeredQuestions = 0;
        $correctAnswersCount = 0;
        $totalScore = 0;

        foreach ($attempt->exam->examQuestions as $examQuestion) {
            $question = $examQuestion->question;

            if ($question === null) {
                continue;
            }

            $maxScore += (int) $examQuestion->max_score;

            $answer = $answers->get($question->id) ?? new ExamAttemptAnswer([
                'exam_attempt_id' => $attempt->id,
                'question_id' => $question->id,
            ]);

            $correctChoice = $question->choices->first(fn (QuestionChoice $choice): bool => $choice->is_correct);
            $selectedChoiceId = filled($answer->selected_answer) ? (int) $answer->selected_answer : null;
            $selectedChoice = $question->choices->first(fn (QuestionChoice $choice): bool => $choice->id === $selectedChoiceId);
            $isAnswered = $selectedChoiceId !== null;
            $isCorrect = $isAnswered && $correctChoice instanceof QuestionChoice && $correctChoice->id === $selectedChoiceId;
            $awardedScore = $isCorrect ? (int) $examQuestion->max_score : 0;

            if ($isAnswered) {
                $answeredQuestions++;
            }

            if ($isCorrect) {
                $correctAnswersCount++;
                $totalScore += $awardedScore;
            }

            $answer->exam_attempt_id = $attempt->id;
            $answer->question_id = $question->id;
            $answer->selected_answer = $isAnswered ? (string) $selectedChoiceId : null;
            $answer->is_correct = $isAnswered ? $isCorrect : false;
            $answer->awarded_score = $awardedScore;
            $answer->max_score = (int) $examQuestion->max_score;
            $answer->answer_meta = [
                'question_prompt' => $question->prompt,
                'selected_choice_id' => $selectedChoice?->id,
                'selected_choice_content' => $selectedChoice?->content,
                'correct_choice_id' => $correctChoice?->id,
                'correct_choice_content' => $correctChoice?->content,
                'explanation' => $question->explanation,
            ];
            $answer->save();
        }

        $wrongCount = max(0, $answeredQuestions - $correctAnswersCount);
        $skippedCount = max(0, $totalQuestions - $answeredQuestions);

        return [
            'total_questions' => $totalQuestions,
            'answered_questions' => $answeredQuestions,
            'correct_answers_count' => $correctAnswersCount,
            'wrong_count' => $wrongCount,
            'skipped_count' => $skippedCount,
            'total_score' => $totalScore,
            'max_score' => $maxScore,
        ];
    }
}
