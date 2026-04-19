<?php

namespace App\Modules\Support\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;

class StoreForumThreadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'min:10'],
            'attachments' => ['nullable', 'array', 'max:3'],
            'attachments.*' => ['file', 'mimetypes:image/jpeg,image/png,image/webp,audio/mpeg,audio/mp3,audio/wav,audio/webm,audio/ogg', 'max:12288'],
        ];
    }
}
