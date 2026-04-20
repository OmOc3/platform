<?php

namespace App\Modules\Commerce\Http\Requests\Student;

use App\Shared\Enums\PaymentStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CompleteFakePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in([
                PaymentStatus::Paid->value,
                PaymentStatus::Failed->value,
                PaymentStatus::Canceled->value,
            ])],
        ];
    }
}
