<?php

namespace App\Modules\Identity\Http\Requests\Admin\Settings;

use App\Modules\Identity\Models\Setting;
use Illuminate\Validation\Rule;

class UpdateSettingRequest extends StoreSettingRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var Setting $setting */
        $setting = $this->route('setting');

        $rules = parent::rules();
        $rules['key'] = [
            'required',
            'string',
            'max:255',
            Rule::unique('settings', 'key')
                ->ignore($setting->id)
                ->where(fn ($query) => $query->where('group', $this->input('group'))),
        ];

        return $rules;
    }
}
