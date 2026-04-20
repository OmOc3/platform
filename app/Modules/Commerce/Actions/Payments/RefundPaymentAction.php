<?php

namespace App\Modules\Commerce\Actions\Payments;

use App\Modules\Commerce\Models\Entitlement;
use App\Modules\Commerce\Models\Payment;
use App\Shared\Contracts\AuditLogger;
use App\Shared\Enums\OrderStatus;
use App\Shared\Enums\PaymentStatus;
use App\Shared\Enums\ShipmentStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RefundPaymentAction
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
        private readonly \App\Modules\Commerce\Actions\Orders\TransitionOrderAction $transitionOrderAction,
    ) {
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array{payment: Payment, order: \App\Modules\Commerce\Models\Order}
     */
    public function execute(Payment $payment, mixed $actor = null, array $payload = []): array
    {
        return DB::transaction(function () use ($payment, $actor, $payload): array {
            /** @var Payment $lockedPayment */
            $lockedPayment = Payment::query()
                ->with(['order.items.entitlement', 'order.shipment'])
                ->lockForUpdate()
                ->findOrFail($payment->id);

            if (! in_array($lockedPayment->status, [PaymentStatus::Paid, PaymentStatus::Refunded], true)) {
                throw ValidationException::withMessages([
                    'payment' => ['لا يمكن رد هذه العملية من حالتها الحالية.'],
                ]);
            }

            $oldValues = $lockedPayment->toArray();

            if ($lockedPayment->status !== PaymentStatus::Refunded) {
                $lockedPayment->status = PaymentStatus::Refunded;
                $lockedPayment->refunded_at ??= now();
                $lockedPayment->failure_code = null;
                $lockedPayment->failure_message = null;
                $lockedPayment->meta = array_merge((array) $lockedPayment->meta, array_filter([
                    'refund_reason' => $payload['reason'] ?? null,
                    'refunded_via' => $payload['source'] ?? 'admin',
                ], fn (mixed $value): bool => $value !== null));
                $lockedPayment->save();

                $this->auditLogger->log(
                    event: 'commerce.payment.refunded',
                    actor: $actor,
                    subject: $lockedPayment,
                    oldValues: $oldValues,
                    newValues: $lockedPayment->fresh()->toArray(),
                    meta: [
                        'order_id' => $lockedPayment->order_id,
                    ],
                );
            }

            foreach ($lockedPayment->order->items as $item) {
                if (! $item->entitlement instanceof Entitlement) {
                    continue;
                }

                $item->entitlement->update([
                    'status' => 'refunded',
                    'ends_at' => now(),
                    'meta' => array_merge((array) $item->entitlement->meta, [
                        'refunded_payment_id' => $lockedPayment->id,
                    ]),
                ]);
            }

            if ($lockedPayment->order->shipment && ! in_array($lockedPayment->order->shipment->status, [ShipmentStatus::Delivered, ShipmentStatus::Returned, ShipmentStatus::Canceled], true)) {
                $lockedPayment->order->shipment->update([
                    'status' => ShipmentStatus::Canceled,
                    'canceled_at' => now(),
                ]);
            }

            $order = $lockedPayment->order->fresh(['items.product.package', 'shipment']);

            if ($order->status !== OrderStatus::Refunded) {
                $this->transitionOrderAction->execute($order, OrderStatus::Refunded, $actor);
            }

            return [
                'payment' => $lockedPayment->fresh('order.items.entitlement'),
                'order' => $order->fresh(['items.entitlement', 'shipment']),
            ];
        });
    }
}
