<?php

namespace App\Modules\Academic\Http\Requests\Admin\Exams;

use App\Modules\Academic\Models\Exam;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateExamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var Exam $exam */
        $exam = $this->route('exam');

        return [
            'lecture_id' => ['nullable', 'exists:lectures,id'],
            'grade_id' => ['required', 'exists:grades,id'],
            'track_id' => ['nullable', 'exists:tracks,id'],
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('exams', 'slug')->ignore($exam->id)],
            'short_description' => ['nullable', 'string', 'max:255'],
            'long_description' => ['nullable', 'string'],
            'thumbnail_url' => ['nullable', 'url'],
            'price_amount' => ['required', 'integer', 'min:0'],
            'currency' => ['required', 'string', 'size:3'],
            'duration_minutes' => ['nullable', 'integer', 'min:1'],
            'published_at' => ['nullable', 'date'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'is_featured' => ['nullable', 'boolean'],
            'is_free' => ['nullable', 'boolean'],
            'metadata' => ['nullable', 'array'],
            'max_attempts' => ['nullable', 'integer', 'min:1', 'max:10'],
            'questions' => ['nullable', 'array'],
            'questions.*.question_id' => ['nullable', 'integer', 'exists:questions,id'],
            'questions.*.prompt' => ['required', 'string'],
            'questions.*.explanation' => ['nullable', 'string'],
            'questions.*.max_score' => ['required', 'integer', 'min:1', 'max:100'],
            'questions.*.choices' => ['required', 'array', 'min:2'],
            'questions.*.choices.*.choice_id' => ['nullable', 'integer', 'exists:question_choices,id'],
            'questions.*.choices.*.content' => ['required', 'string', 'max:1000'],
            'questions.*.choices.*.is_correct' => ['required', 'boolean'],
            'questions.*.choices.*.sort_order' => ['required', 'integer', 'min:1'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $questions = collect($this->input('questions', []))
            ->map(function (array $question): array {
                $correctChoice = (string) ($question['correct_choice'] ?? '');

                return [
                    'question_id' => filled($question['question_id'] ?? null) ? (int) $question['question_id'] : null,
                    'prompt' => trim((string) ($question['prompt'] ?? '')),
                    'explanation' => filled($question['explanation'] ?? null) ? trim((string) $question['explanation']) : null,
                    'max_score' => max(1, (int) ($question['max_score'] ?? 1)),
                    'choices' => collect($question['choices'] ?? [])
                        ->map(function (array $choice, int $index) use ($correctChoice): array {
                            return [
                                'choice_id' => filled($choice['choice_id'] ?? null) ? (int) $choice['choice_id'] : null,
                                'content' => trim((string) ($choice['content'] ?? '')),
                                'is_correct' => $correctChoice === (string) $index,
                                'sort_order' => $index + 1,
                            ];
                        })
                        ->values()
                        ->all(),
                ];
            })
            ->values()
            ->all();

        $metadata = array_filter([
            ...((array) $this->input('metadata', [])),
            'max_attempts' => $this->filled('max_attempts') ? max(1, $this->integer('max_attempts')) : null,
        ], fn (mixed $value): bool => $value !== null && $value !== '');

        $this->merge([
            'lecture_id' => $this->filled('lecture_id') ? $this->integer('lecture_id') : null,
            'track_id' => $this->filled('track_id') ? $this->integer('track_id') : null,
            'sort_order' => $this->input('sort_order', 0),
            'is_active' => $this->boolean('is_active'),
            'is_featured' => $this->boolean('is_featured'),
            'is_free' => $this->boolean('is_free'),
            'currency' => strtoupper((string) $this->input('currency', 'EGP')),
            'questions' => $questions,
            'metadata' => $metadata === [] ? null : $metadata,
        ]);
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            foreach ((array) $this->input('questions', []) as $index => $question) {
                $correctChoices = collect($question['choices'] ?? [])->filter(fn (array $choice): bool => (bool) ($choice['is_correct'] ?? false));

                if ($correctChoices->count() !== 1) {
                    $validator->errors()->add("questions.{$index}.choices", 'يجب تحديد اختيار صحيح واحد لكل سؤال.');
                }
            }
        });
    }
}
