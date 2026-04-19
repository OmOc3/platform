<?php

namespace App\Modules\Students\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOwnProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $student = $this->user('student');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('students', 'email')->ignore($student?->id)],
            'phone' => ['required', 'string', 'max:30', Rule::unique('students', 'phone')->ignore($student?->id)],
            'parent_phone' => ['nullable', 'string', 'max:30'],
            'governorate' => ['nullable', 'string', 'max:255'],
        ];
    }
}
