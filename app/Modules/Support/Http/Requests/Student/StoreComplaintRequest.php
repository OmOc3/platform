<?php

namespace App\Modules\Support\Http\Requests\Student;

use App\Modules\Support\Enums\ComplaintType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreComplaintRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', Rule::enum(ComplaintType::class)],
            'content' => ['required', 'string', 'min:10', 'max:5000'],
        ];
    }
}
