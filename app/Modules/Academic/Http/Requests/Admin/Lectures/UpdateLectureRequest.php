<?php

namespace App\Modules\Academic\Http\Requests\Admin\Lectures;

use App\Modules\Academic\Models\Lecture;
use App\Shared\Enums\ContentKind;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class UpdateLectureRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var Lecture $lecture */
        $lecture = $this->route('lecture');

        return [
            'grade_id' => ['required', 'exists:grades,id'],
            'track_id' => ['nullable', 'exists:tracks,id'],
            'curriculum_section_id' => ['nullable', 'exists:curriculum_sections,id'],
            'lecture_section_id' => ['nullable', 'exists:lecture_sections,id'],
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('lectures', 'slug')->ignore($lecture->id)],
            'short_description' => ['nullable', 'string', 'max:255'],
            'long_description' => ['nullable', 'string'],
            'thumbnail_url' => ['nullable', 'url'],
            'type' => ['required', new Enum(ContentKind::class)],
            'price_amount' => ['required', 'integer', 'min:0'],
            'currency' => ['required', 'string', 'size:3'],
            'duration_minutes' => ['nullable', 'integer', 'min:1'],
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
            'track_id' => $this->filled('track_id') ? $this->integer('track_id') : null,
            'curriculum_section_id' => $this->filled('curriculum_section_id') ? $this->integer('curriculum_section_id') : null,
            'lecture_section_id' => $this->filled('lecture_section_id') ? $this->integer('lecture_section_id') : null,
            'sort_order' => $this->input('sort_order', 0),
            'is_active' => $this->boolean('is_active'),
            'is_featured' => $this->boolean('is_featured'),
            'is_free' => $this->boolean('is_free'),
            'currency' => strtoupper((string) $this->input('currency', 'EGP')),
        ]);
    }
}
