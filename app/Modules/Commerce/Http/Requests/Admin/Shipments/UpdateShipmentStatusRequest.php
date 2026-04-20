<?php

namespace App\Modules\Commerce\Http\Requests\Admin\Shipments;

use App\Shared\Enums\ShipmentStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateShipmentStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', new Enum(ShipmentStatus::class)],
            'carrier_name' => ['nullable', 'string', 'max:255'],
            'carrier_reference' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
