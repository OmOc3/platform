<?php

namespace App\Modules\Academic\Http\Requests\Admin\Grades;

use App\Modules\Academic\Models\Grade;
use Illuminate\Validation\Rule;

class UpdateGradeRequest extends StoreGradeRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var Grade $grade */
        $grade = $this->route('grade');

        $rules = parent::rules();
        $rules['code'] = ['required', 'string', 'max:255', Rule::unique('grades', 'code')->ignore($grade->id)];

        return $rules;
    }
}
