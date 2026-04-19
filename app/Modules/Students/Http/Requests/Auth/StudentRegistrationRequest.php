<?php

namespace App\Modules\Students\Http\Requests\Auth;

use App\Modules\Academic\Models\Track;
use App\Modules\Students\Enums\StudentSourceType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StudentRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:students,email'],
            'phone' => ['required', 'string', 'max:30', 'unique:students,phone'],
            'parent_phone' => ['nullable', 'string', 'max:30'],
            'governorate' => ['nullable', 'string', 'max:255'],
            'grade_id' => ['required', 'exists:grades,id'],
            'track_id' => [
                'required',
                'exists:tracks,id',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $track = Track::query()->find($value);

                    if ($track && (int) $track->grade_id !== (int) $this->input('grade_id')) {
                        $fail('المسار المختار لا ينتمي إلى الصف المحدد.');
                    }
                },
            ],
            'is_azhar' => ['nullable', 'boolean'],
            'source_type' => ['nullable', Rule::enum(StudentSourceType::class)],
            'password' => ['required', 'confirmed', Password::min(8)],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_azhar' => $this->boolean('is_azhar'),
            'source_type' => $this->input('source_type', StudentSourceType::Online->value),
        ]);
    }
}
