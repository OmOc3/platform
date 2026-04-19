<?php

namespace App\Modules\Academic\Http\Requests\Student\ExamAttempts;

use Illuminate\Foundation\Http\FormRequest;

class SaveExamAttemptRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'answers' => ['nullable', 'array'],
            'answers.*' => ['nullable', 'integer', 'exists:question_choices,id'],
        ];
    }
}
