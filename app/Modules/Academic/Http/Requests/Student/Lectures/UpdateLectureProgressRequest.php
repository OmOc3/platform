<?php

namespace App\Modules\Academic\Http\Requests\Student\Lectures;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLectureProgressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'position_seconds' => ['nullable', 'integer', 'min:0'],
            'consumed_seconds' => ['nullable', 'integer', 'min:0'],
            'completion_percent' => ['nullable', 'numeric', 'between:0,100'],
        ];
    }
}
