<?php

namespace App\Modules\Academic\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Academic\Models\Exam;
use App\Modules\Academic\Models\ExamAttempt;
use App\Modules\Academic\Queries\ExamAttemptsIndexQuery;
use App\Shared\Enums\ExamAttemptStatus;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ExamAttemptController extends Controller
{
    public function __construct(private readonly ExamAttemptsIndexQuery $examAttemptsIndexQuery)
    {
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', ExamAttempt::class);

        return view('admin.academic.exam-attempts.index', [
            'attempts' => $this->examAttemptsIndexQuery->builder($request)->paginate(15)->withQueryString(),
            'exams' => Exam::query()->orderBy('title')->get(),
            'statuses' => ExamAttemptStatus::cases(),
        ]);
    }

    public function show(ExamAttempt $examAttempt): View
    {
        $this->authorize('view', $examAttempt);

        $examAttempt->load(['exam.grade', 'exam.track', 'student', 'answers.question.choices']);

        return view('admin.academic.exam-attempts.show', [
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

        return $examAttempt->exam->examQuestions()
            ->with('question.choices')
            ->orderBy('sort_order')
            ->get()
            ->map(function ($examQuestion) use ($answers): array {
                $question = $examQuestion->question;
                $answer = $answers->get($examQuestion->question_id);

                return [
                    'question' => $question,
                    'answer' => $answer,
                    'selected_choice' => $answer?->answer_meta['selected_choice_content'] ?? 'لم تتم الإجابة',
                    'correct_choice' => $answer?->answer_meta['correct_choice_content'] ?? null,
                    'explanation' => $answer?->answer_meta['explanation'] ?? $question?->explanation,
                    'is_correct' => (bool) ($answer?->is_correct ?? false),
                    'awarded_score' => (int) ($answer?->awarded_score ?? 0),
                    'max_score' => (int) $examQuestion->max_score,
                ];
            })
            ->all();
    }
}
