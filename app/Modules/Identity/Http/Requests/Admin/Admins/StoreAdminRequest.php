<?php

namespace App\Modules\Identity\Http\Requests\Admin\Admins;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class StoreAdminRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:admins,email'],
            'phone' => ['nullable', 'string', 'max:30'],
            'job_title' => ['nullable', 'string', 'max:255'],
            'locale' => ['required', 'string', 'max:5'],
            'is_active' => ['nullable', 'boolean'],
            'password' => ['required', 'confirmed', Password::min(8)],
            'role_names' => ['required', 'array', 'min:1'],
            'role_names.*' => ['string', 'exists:roles,name'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
            'locale' => $this->input('locale', 'ar'),
        ]);
    }
}
