<?php

namespace App\Modules\Academic\Http\Requests\Admin\Tracks;

use App\Modules\Academic\Models\Track;
use Illuminate\Validation\Rule;

class UpdateTrackRequest extends StoreTrackRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var Track $track */
        $track = $this->route('track');

        $rules = parent::rules();
        $rules['code'] = ['required', 'string', 'max:255', Rule::unique('tracks', 'code')->ignore($track->id)];

        return $rules;
    }
}
