<?php

namespace App\Shared\Services;

use App\Modules\Commerce\Models\Order;
use App\Modules\Commerce\Models\Payment;
use App\Shared\Contracts\PaymentProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FakePaymentProvider implements PaymentProvider
{
    public function initiate(Payment $payment, Order $order, array $context = []): array
    {
        $providerReference = $payment->provider_reference ?: 'fake_pay_'.$payment->id.'_'.Str::lower(Str::random(10));
        $expiresAt = now()->addMinutes((int) config('services.commerce.fake.expires_in_minutes', 30));

        return [
            'provider_reference' => $providerReference,
            'checkout_url' => route('student.order-payments.fake.show', $payment),
            'expires_at' => $expiresAt,
            'meta' => [
                'order_uuid' => $order->uuid,
                'kind' => $order->kind->value,
                'student_id' => $order->student_id,
            ],
        ];
    }

    public function normalizeWebhook(Request $request): array
    {
        $payload = $request->all();

        return [
            'event_key' => (string) ($payload['event_key'] ?? ''),
            'provider_reference' => (string) ($payload['provider_reference'] ?? ''),
            'provider_transaction_reference' => $payload['provider_transaction_reference'] ?? null,
            'status' => (string) ($payload['status'] ?? ''),
            'meta' => [
                'note' => $payload['note'] ?? null,
                'trigger' => $payload['trigger'] ?? 'provider_webhook',
            ],
            'payload' => $payload,
        ];
    }
}
