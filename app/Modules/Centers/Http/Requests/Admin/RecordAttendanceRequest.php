<?php

namespace App\Modules\Centers\Http\Requests\Admin;

use App\Shared\Enums\AttendanceStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class RecordAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'records' => ['required', 'array', 'min:1'],
            'records.*.student_id' => ['required', 'integer', 'exists:students,id'],
            'records.*.attendance_status' => ['required', new Enum(AttendanceStatus::class)],
            'records.*.exam_status_label' => ['nullable', 'string', 'max:255'],
            'records.*.score' => ['nullable', 'numeric', 'min:0'],
            'records.*.max_score' => ['nullable', 'numeric', 'min:0'],
            'records.*.notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
