<?php

namespace App\Modules\Commerce\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;

class AddCartItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:10'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'quantity' => $this->integer('quantity') ?: 1,
        ]);
    }
}
