<?php

namespace App\Modules\Academic\Http\Requests\Admin\CurriculumSections;

use App\Modules\Academic\Models\CurriculumSection;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCurriculumSectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var CurriculumSection $section */
        $section = $this->route('curriculum_section');

        return [
            'grade_id' => ['required', 'exists:grades,id'],
            'track_id' => ['nullable', 'exists:tracks,id'],
            'name_ar' => ['required', 'string', 'max:255'],
            'name_en' => ['nullable', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('curriculum_sections', 'slug')->ignore($section->id)],
            'description' => ['nullable', 'string'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'track_id' => $this->filled('track_id') ? $this->integer('track_id') : null,
            'sort_order' => $this->input('sort_order', 0),
            'is_active' => $this->boolean('is_active'),
        ]);
    }
}
