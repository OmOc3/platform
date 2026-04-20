<?php

namespace App\Modules\Students\Http\Requests\Admin;

use App\Modules\Academic\Models\Track;
use App\Modules\Students\Enums\StudentSourceType;
use App\Shared\Enums\StudentStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $student = $this->route('student');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('students', 'email')->ignore($student?->id)],
            'phone' => ['required', 'string', 'max:30', Rule::unique('students', 'phone')->ignore($student?->id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'parent_phone' => ['nullable', 'string', 'max:30'],
            'governorate' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::enum(StudentStatus::class)],
            'status_reason' => ['nullable', 'string', 'max:255'],
            'source_type' => ['required', Rule::enum(StudentSourceType::class)],
            'is_azhar' => ['nullable', 'boolean'],
            'grade_id' => ['nullable', 'exists:grades,id'],
            'track_id' => [
                'nullable',
                'exists:tracks,id',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (! $value || ! $this->input('grade_id')) {
                        return;
                    }

                    $track = Track::query()->find($value);

                    if ($track && (int) $track->grade_id !== (int) $this->input('grade_id')) {
                        $fail('المسار المختار لا ينتمي إلى الصف المحدد.');
                    }
                },
            ],
            'center_id' => ['nullable', 'exists:educational_centers,id'],
            'group_id' => ['nullable', 'exists:educational_groups,id'],
            'owner_admin_id' => ['nullable', 'exists:admins,id'],
            'notes' => ['nullable', 'string'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_azhar' => $this->boolean('is_azhar'),
        ]);
    }
}
