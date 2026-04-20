<?php

namespace App\Modules\Support\Http\Requests\Admin;

use App\Shared\Enums\ComplaintStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateComplaintRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', new Enum(ComplaintStatus::class)],
            'admin_notes' => ['nullable', 'string'],
        ];
    }
}
