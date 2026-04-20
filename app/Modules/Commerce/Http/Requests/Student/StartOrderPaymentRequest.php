<?php

namespace App\Modules\Commerce\Http\Requests\Student;

use App\Modules\Commerce\Models\Order;
use App\Shared\Enums\OrderKind;
use Illuminate\Foundation\Http\FormRequest;

class StartOrderPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var Order|null $order */
        $order = $this->route('order');
        $bookOrder = $order?->kind === OrderKind::Book;

        return [
            'provider' => ['required', 'string', 'max:50'],
            'shipping' => ['nullable', 'array'],
            'shipping.recipient_name' => [$bookOrder ? 'required' : 'nullable', 'string', 'max:255'],
            'shipping.phone' => [$bookOrder ? 'required' : 'nullable', 'string', 'max:30'],
            'shipping.alternate_phone' => ['nullable', 'string', 'max:30'],
            'shipping.governorate' => [$bookOrder ? 'required' : 'nullable', 'string', 'max:255'],
            'shipping.city' => [$bookOrder ? 'required' : 'nullable', 'string', 'max:255'],
            'shipping.address_line1' => [$bookOrder ? 'required' : 'nullable', 'string', 'max:255'],
            'shipping.address_line2' => ['nullable', 'string', 'max:255'],
            'shipping.landmark' => ['nullable', 'string', 'max:255'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'provider' => (string) $this->input('provider', config('services.commerce.default_payment_provider', 'fake')),
        ]);
    }
}
