<?php

namespace App\Modules\Commerce\Actions\Payments;

use App\Modules\Commerce\Actions\Orders\FinalizePaidOrderAction;
use App\Modules\Commerce\Actions\Orders\TransitionOrderAction;
use App\Modules\Commerce\Models\Payment;
use App\Modules\Commerce\Models\PaymentWebhookReceipt;
use App\Shared\Contracts\AuditLogger;
use App\Shared\Contracts\PaymentProviderRegistry;
use App\Shared\Enums\OrderStatus;
use App\Shared\Enums\PaymentStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class HandlePaymentWebhookAction
{
    public function __construct(
        private readonly PaymentProviderRegistry $paymentProviderRegistry,
        private readonly TransitionOrderAction $transitionOrderAction,
        private readonly FinalizePaidOrderAction $finalizePaidOrderAction,
        private readonly RefundPaymentAction $refundPaymentAction,
        private readonly AuditLogger $auditLogger,
    ) {
    }

    /**
     * @param  Request|array<string, mixed>  $payload
     * @return array{receipt: PaymentWebhookReceipt, payment: ?Payment, processed: bool}
     */
    public function execute(string $providerName, Request|array $payload, mixed $actor = null): array
    {
        $normalized = $payload instanceof Request
            ? $this->paymentProviderRegistry->driver($providerName)->normalizeWebhook($payload)
            : $payload;

        if (! filled($normalized['event_key'] ?? null) || ! filled($normalized['provider_reference'] ?? null) || ! filled($normalized['status'] ?? null)) {
            throw ValidationException::withMessages([
                'webhook' => ['بيانات webhook غير مكتملة.'],
            ]);
        }

        $status = PaymentStatus::from((string) $normalized['status']);

        return DB::transaction(function () use ($providerName, $normalized, $status, $actor): array {
            $receipt = PaymentWebhookReceipt::query()
                ->where('provider', $providerName)
                ->where('event_key', $normalized['event_key'])
                ->lockForUpdate()
                ->first();

            if ($receipt && $receipt->processed_at) {
                return [
                    'receipt' => $receipt->fresh('payment.order'),
                    'payment' => $receipt->payment,
                    'processed' => false,
                ];
            }

            $payment = Payment::query()
                ->with(['order.items.product.package', 'order.items.entitlement', 'order.shipment'])
                ->where('provider', $providerName)
                ->where('provider_reference', $normalized['provider_reference'])
                ->lockForUpdate()
                ->first();

            $receipt ??= new PaymentWebhookReceipt([
                'provider' => $providerName,
                'event_key' => $normalized['event_key'],
            ]);

            $receipt->fill([
                'payment_id' => $payment?->id,
                'order_id' => $payment?->order_id,
                'status' => $status->value,
                'payload' => (array) ($normalized['payload'] ?? []),
                'meta' => (array) ($normalized['meta'] ?? []),
                'processed_at' => now(),
            ]);
            $receipt->save();

            if (! $payment instanceof Payment) {
                return [
                    'receipt' => $receipt->fresh(),
                    'payment' => null,
                    'processed' => false,
                ];
            }

            $oldValues = $payment->toArray();

            $this->applyPaymentStatus($payment, $status, $normalized);

            if ($payment->isDirty()) {
                $payment->save();

                $this->auditLogger->log(
                    event: 'commerce.payment.status_changed',
                    actor: $actor,
                    subject: $payment,
                    oldValues: $oldValues,
                    newValues: $payment->fresh()->toArray(),
                    meta: [
                        'order_id' => $payment->order_id,
                        'webhook_event' => $normalized['event_key'],
                        'provider' => $providerName,
                    ],
                );
            }

            $freshPayment = $payment->fresh(['order.items.product.package', 'order.items.entitlement', 'order.shipment']);

            if ($status === PaymentStatus::Paid && $freshPayment->order->status === OrderStatus::PendingPayment) {
                $this->transitionOrderAction->execute($freshPayment->order, OrderStatus::Paid, $actor);
                $this->finalizePaidOrderAction->execute($freshPayment->order->fresh(['items.product.package', 'shipment']), $actor);
            }

            if ($status === PaymentStatus::Refunded) {
                $this->refundPaymentAction->execute($freshPayment, $actor, [
                    'source' => 'webhook',
                    'reason' => data_get($normalized, 'meta.note'),
                ]);
            }

            return [
                'receipt' => $receipt->fresh('payment.order'),
                'payment' => $freshPayment->fresh(['order.items.entitlement', 'order.shipment']),
                'processed' => true,
            ];
        });
    }

    /**
     * @param  array<string, mixed>  $normalized
     */
    private function applyPaymentStatus(Payment $payment, PaymentStatus $status, array $normalized): void
    {
        if ($payment->status === PaymentStatus::Refunded && $status !== PaymentStatus::Refunded) {
            return;
        }

        if ($payment->status === PaymentStatus::Paid && in_array($status, [PaymentStatus::Pending, PaymentStatus::RequiresAction, PaymentStatus::Failed, PaymentStatus::Canceled], true)) {
            return;
        }

        $payment->status = $status;
        $payment->provider_transaction_reference = $normalized['provider_transaction_reference'] ?? $payment->provider_transaction_reference;
        $payment->meta = array_merge((array) $payment->meta, (array) ($normalized['meta'] ?? []));

        match ($status) {
            PaymentStatus::Paid => $payment->paid_at ??= now(),
            PaymentStatus::Failed => $payment->failed_at ??= now(),
            PaymentStatus::Canceled => $payment->canceled_at ??= now(),
            PaymentStatus::Refunded => $payment->refunded_at ??= now(),
            default => null,
        };

        if ($status === PaymentStatus::Failed) {
            $payment->failure_code = (string) data_get($normalized, 'meta.failure_code', 'provider_failed');
            $payment->failure_message = data_get($normalized, 'meta.note');
        }

        if (in_array($status, [PaymentStatus::Paid, PaymentStatus::Refunded], true)) {
            $payment->failure_code = null;
            $payment->failure_message = null;
        }
    }
}
