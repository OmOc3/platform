<?php

namespace App\Modules\Academic\Http\Requests\Admin\LectureSections;

use App\Modules\Academic\Models\LectureSection;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLectureSectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var LectureSection $section */
        $section = $this->route('lecture_section');

        return [
            'grade_id' => ['required', 'exists:grades,id'],
            'track_id' => ['nullable', 'exists:tracks,id'],
            'curriculum_section_id' => ['nullable', 'exists:curriculum_sections,id'],
            'name_ar' => ['required', 'string', 'max:255'],
            'name_en' => ['nullable', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('lecture_sections', 'slug')->ignore($section->id)],
            'description' => ['nullable', 'string'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'track_id' => $this->filled('track_id') ? $this->integer('track_id') : null,
            'curriculum_section_id' => $this->filled('curriculum_section_id') ? $this->integer('curriculum_section_id') : null,
            'sort_order' => $this->input('sort_order', 0),
            'is_active' => $this->boolean('is_active'),
        ]);
    }
}
