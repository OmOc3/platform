<?php

namespace App\Modules\Academic\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Modules\Academic\Http\Requests\Student\ExamAttempts\SaveExamAttemptRequest;
use App\Modules\Academic\Http\Requests\Student\ExamAttempts\SubmitExamAttemptRequest;
use App\Modules\Academic\Models\Exam;
use App\Modules\Academic\Models\ExamAttempt;
use App\Shared\Contracts\ExamAttemptService;
use App\Shared\Enums\ExamAttemptStatus;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ExamAttemptController extends Controller
{
    public function __construct(private readonly ExamAttemptService $examAttemptService)
    {
    }

    public function start(Exam $exam): RedirectResponse
    {
        $attempt = $this->examAttemptService->start([
            'exam' => $exam,
            'student' => auth('student')->user(),
        ]);

        return redirect()
            ->route('student.exam-attempts.show', $attempt)
            ->with('status', 'تم بدء المحاولة ويمكنك الآن الإجابة وحفظ التقدم.');
    }

    public function show(ExamAttempt $examAttempt): View|RedirectResponse
    {
        $this->authorize('view', $examAttempt);

        $student = auth('student')->user();

        if ($examAttempt->status === ExamAttemptStatus::Graded) {
            return redirect()->route('student.exam-attempts.result', $examAttempt);
        }

        if ($examAttempt->status === ExamAttemptStatus::InProgress && $examAttempt->expiresAt()?->isPast()) {
            $attempt = $this->examAttemptService->submit([
                'attempt' => $examAttempt,
                'student' => $student,
                'submitted_by_timer' => true,
            ]);

            return redirect()
                ->route('student.exam-attempts.result', $attempt)
                ->with('status', 'انتهى الوقت وتم إرسال المحاولة تلقائيًا.');
        }

        $examAttempt->load(['exam.grade', 'exam.track', 'exam.examQuestions.question.choices', 'answers']);

        return view('student.exams.attempt', [
            'examAttempt' => $examAttempt,
            'answerMap' => $examAttempt->answers->pluck('selected_answer', 'question_id')->all(),
            'expiresAt' => $examAttempt->expiresAt(),
        ]);
    }

    public function save(SaveExamAttemptRequest $request, ExamAttempt $examAttempt): RedirectResponse
    {
        $this->authorize('update', $examAttempt);

        $attempt = $this->examAttemptService->saveProgress([
            'attempt' => $examAttempt,
            'student' => auth('student')->user(),
            'answers' => $request->validated('answers', []),
        ]);

        if ($attempt->status === ExamAttemptStatus::Graded) {
            return redirect()
                ->route('student.exam-attempts.result', $attempt)
                ->with('status', 'انتهى وقت الاختبار أثناء الحفظ وتم إرسال الإجابات المتاحة.');
        }

        return redirect()
            ->route('student.exam-attempts.show', $attempt)
            ->with('status', 'تم حفظ التقدم الحالي بنجاح.');
    }

    public function submit(SubmitExamAttemptRequest $request, ExamAttempt $examAttempt): RedirectResponse
    {
        $this->authorize('view', $examAttempt);

        $attempt = $this->examAttemptService->submit([
            'attempt' => $examAttempt,
            'student' => auth('student')->user(),
            'answers' => $request->validated('answers', []),
        ]);

        return redirect()
            ->route('student.exam-attempts.result', $attempt)
            ->with('status', 'تم إرسال الاختبار وإظهار النتيجة الحالية.');
    }

    public function result(ExamAttempt $examAttempt): View|RedirectResponse
    {
        $this->authorize('view', $examAttempt);

        if ($examAttempt->status !== ExamAttemptStatus::Graded) {
            return redirect()->route('student.exam-attempts.show', $examAttempt);
        }

        $examAttempt->load(['exam.grade', 'exam.track', 'exam.examQuestions.question.choices', 'answers.question.choices']);

        return view('student.exams.result', [
            'examAttempt' => $examAttempt,
            'questionResults' => $this->questionResults($examAttempt),
            'wrongCount' => (int) data_get($examAttempt->result_meta, 'wrong_count', 0),
            'skippedCount' => (int) data_get($examAttempt->result_meta, 'skipped_count', 0),
        ]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function questionResults(ExamAttempt $examAttempt): array
    {
        $answers = $examAttempt->answers->keyBy('question_id');

        return $examAttempt->exam->examQuestions
            ->map(function ($examQuestion, $index) use ($answers): array {
                $question = $examQuestion->question;
                $answer = $answers->get($examQuestion->question_id);
                $questionMeta = (array) ($question?->metadata ?? []);
                $answerMeta = (array) ($answer?->answer_meta ?? []);

                return [
                    'number' => $index + 1,
                    'question' => $question,
                    'answer' => $answer,
                    'selected_choice' => $answer?->answer_meta['selected_choice_content'] ?? 'لم تتم الإجابة',
                    'correct_choice' => $answer?->answer_meta['correct_choice_content'] ?? null,
                    'explanation' => $answer?->answer_meta['explanation'] ?? $question?->explanation,
                    'important_note' => $answerMeta['important_note'] ?? $questionMeta['important_note'] ?? null,
                    'question_image' => $this->resolveImagePath($questionMeta, [
                        'question_image_path',
                        'image_path',
                        'question_image',
                        'image',
                    ]),
                    'solution_image' => $this->resolveImagePath(array_merge($questionMeta, $answerMeta), [
                        'solution_image_path',
                        'answer_image_path',
                        'explanation_image_path',
                        'solution_image',
                        'answer_image',
                        'explanation_image',
                    ]),
                    'choices' => $question?->choices
                        ?->map(fn ($choice): array => [
                            'content' => $choice->content,
                            'is_correct' => (bool) $choice->is_correct,
                            'is_selected' => (int) ($answer?->selected_answer ?? 0) === $choice->id,
                        ])
                        ->values()
                        ->all() ?? [],
                    'is_correct' => (bool) ($answer?->is_correct ?? false),
                    'awarded_score' => (int) ($answer?->awarded_score ?? 0),
                    'max_score' => (int) $examQuestion->max_score,
                ];
            })
            ->all();
    }

    /**
     * @param  array<string, mixed>  $payload
     * @param  array<int, string>  $keys
     */
    private function resolveImagePath(array $payload, array $keys): ?string
    {
        foreach ($keys as $key) {
            $value = data_get($payload, $key);

            if (is_string($value) && $value !== '') {
                return $value;
            }
        }

        return null;
    }
}
