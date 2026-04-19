<?php

namespace App\Modules\Academic\Http\Requests\Admin\Exams;

use App\Modules\Academic\Models\Exam;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'question_count' => ['nullable', 'integer', 'min:0'],
            'published_at' => ['nullable', 'date'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'is_featured' => ['nullable', 'boolean'],
            'is_free' => ['nullable', 'boolean'],
            'metadata' => ['nullable', 'array'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'lecture_id' => $this->filled('lecture_id') ? $this->integer('lecture_id') : null,
            'track_id' => $this->filled('track_id') ? $this->integer('track_id') : null,
            'sort_order' => $this->input('sort_order', 0),
            'is_active' => $this->boolean('is_active'),
            'is_featured' => $this->boolean('is_featured'),
            'is_free' => $this->boolean('is_free'),
            'currency' => strtoupper((string) $this->input('currency', 'EGP')),
        ]);
    }
}
