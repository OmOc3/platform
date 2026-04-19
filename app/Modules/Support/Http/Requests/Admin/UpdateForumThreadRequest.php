<?php

namespace App\Modules\Support\Http\Requests\Admin;

use App\Modules\Support\Enums\ForumThreadStatus;
use App\Modules\Support\Enums\ForumVisibility;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateForumThreadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', new Enum(ForumThreadStatus::class)],
            'visibility' => ['required', new Enum(ForumVisibility::class)],
        ];
    }
}
