<?php

namespace App\Modules\Support\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSupportTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'support_ticket_type_id' => [
                'required',
                Rule::exists('support_ticket_types', 'id')->where(fn ($query) => $query->where('is_active', true)),
            ],
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'min:10'],
        ];
    }
}
