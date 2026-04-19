<?php

namespace App\Modules\Students\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class StudentForgotPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
        ];
    }
}
