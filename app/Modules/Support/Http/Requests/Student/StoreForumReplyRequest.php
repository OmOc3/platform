<?php

namespace App\Modules\Support\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;

class StoreForumReplyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'body' => ['required', 'string', 'min:2'],
            'attachments' => ['nullable', 'array', 'max:3'],
            'attachments.*' => ['file', 'mimetypes:image/jpeg,image/png,image/webp,audio/mpeg,audio/mp3,audio/wav,audio/webm,audio/ogg', 'max:12288'],
        ];
    }
}
