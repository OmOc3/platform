<?php

namespace App\Modules\Commerce\Actions\Payments;

use App\Modules\Commerce\Actions\Orders\TransitionOrderAction;
use App\Modules\Commerce\Models\Order;
use App\Modules\Commerce\Models\Payment;
use App\Shared\Contracts\AuditLogger;
use App\Shared\Contracts\PaymentProviderRegistry;
use App\Shared\Contracts\ShippingFeeCalculator;
use App\Shared\Enums\OrderKind;
use App\Shared\Enums\OrderStatus;
use App\Shared\Enums\PaymentStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StartOrderPaymentAction
{
    public function __construct(
        private readonly PaymentProviderRegistry $paymentProviderRegistry,
        private readonly ShippingFeeCalculator $shippingFeeCalculator,
        private readonly TransitionOrderAction $transitionOrderAction,
        private readonly AuditLogger $auditLogger,
    ) {
    }

    public function execute(Order $order, array $payload, mixed $actor = null): Payment
    {
        return DB::transaction(function () use ($order, $payload, $actor): Payment {
            /** @var Order $lockedOrder */
            $lockedOrder = Order::query()
                ->with(['student', 'items.product.book', 'payments'])
                ->lockForUpdate()
                ->findOrFail($order->id);

            if (! in_array($lockedOrder->status, [OrderStatus::Draft, OrderStatus::PendingPayment], true)) {
                throw ValidationException::withMessages([
                    'order' => ['لا يمكن بدء الدفع لهذا الطلب من حالته الحالية.'],
                ]);
            }

            if ($lockedOrder->items->isEmpty()) {
                throw ValidationException::withMessages([
                    'order' => ['لا يمكن بدء الدفع لطلب لا يحتوي على عناصر.'],
                ]);
            }

            $providerName = (string) ($payload['provider'] ?? config('services.commerce.default_payment_provider', 'fake'));
            $activePayment = $lockedOrder->payments
                ->first(fn (Payment $payment): bool => in_array($payment->status, [PaymentStatus::Pending, PaymentStatus::RequiresAction], true));

            if ($lockedOrder->kind === OrderKind::Book) {
                $snapshot = $this->normalizeShippingSnapshot($lockedOrder, (array) ($payload['shipping'] ?? []));
                $shippingSummary = $this->shippingFeeCalculator->calculate($lockedOrder->student, $lockedOrder->items, $snapshot);

                if (! $shippingSummary['can_deliver']) {
                    throw ValidationException::withMessages([
                        'shipping.governorate' => [$shippingSummary['warning'] ?: 'المحافظة الحالية غير مدعومة لطلب الكتب.'],
                    ]);
                }

                $lockedOrder->meta = array_merge((array) $lockedOrder->meta, [
                    'shipping_address' => $snapshot,
                    'shipping_summary' => [
                        'amount' => $shippingSummary['amount'],
                        'label' => $shippingSummary['label'],
                        'warning' => $shippingSummary['warning'],
                        'supported_governorates' => $shippingSummary['supported_governorates']->all(),
                    ],
                ]);
                $lockedOrder->total_amount = $lockedOrder->subtotal_amount + $shippingSummary['amount'];
                $lockedOrder->save();
            }

            if ($activePayment instanceof Payment && $lockedOrder->status === OrderStatus::PendingPayment) {
                if (! $activePayment->provider_reference || ! $activePayment->checkout_url) {
                    $providerResponse = $this->paymentProviderRegistry->driver($providerName)->initiate($activePayment, $lockedOrder, [
                        'shipping' => data_get($lockedOrder->meta, 'shipping_address', []),
                    ]);

                    $oldValues = $activePayment->toArray();

                    $activePayment->fill([
                        'provider' => $providerName,
                        'amount' => $lockedOrder->total_amount,
                        'currency' => $lockedOrder->currency,
                        'provider_reference' => $providerResponse['provider_reference'],
                        'checkout_url' => $providerResponse['checkout_url'],
                        'expires_at' => $providerResponse['expires_at'],
                        'meta' => array_merge((array) $activePayment->meta, (array) ($providerResponse['meta'] ?? [])),
                    ]);
                    $activePayment->save();

                    $this->auditLogger->log(
                        event: 'commerce.payment.reinitialized',
                        actor: $actor,
                        subject: $activePayment,
                        oldValues: $oldValues,
                        newValues: $activePayment->fresh()->toArray(),
                        meta: [
                            'order_id' => $lockedOrder->id,
                            'order_uuid' => $lockedOrder->uuid,
                        ],
                    );
                }

                return $activePayment->fresh('order.student');
            }

            if ($lockedOrder->status === OrderStatus::Draft) {
                $this->transitionOrderAction->execute($lockedOrder, OrderStatus::PendingPayment, $actor);
                $lockedOrder = $lockedOrder->fresh(['student', 'items.product.book', 'payments']);
            }

            $payment = Payment::query()->create([
                'order_id' => $lockedOrder->id,
                'attempt_number' => ((int) $lockedOrder->payments()->max('attempt_number')) + 1,
                'provider' => $providerName,
                'status' => PaymentStatus::Pending,
                'amount' => $lockedOrder->total_amount,
                'currency' => $lockedOrder->currency,
                'meta' => [
                    'initiated_from' => 'student_checkout',
                ],
            ]);

            $providerResponse = $this->paymentProviderRegistry->driver($providerName)->initiate($payment, $lockedOrder, [
                'shipping' => data_get($lockedOrder->meta, 'shipping_address', []),
            ]);

            $payment->update([
                'provider_reference' => $providerResponse['provider_reference'],
                'checkout_url' => $providerResponse['checkout_url'],
                'expires_at' => $providerResponse['expires_at'],
                'meta' => array_merge((array) $payment->meta, (array) ($providerResponse['meta'] ?? [])),
            ]);

            $this->auditLogger->log(
                event: 'commerce.payment.initiated',
                actor: $actor,
                subject: $payment,
                oldValues: [],
                newValues: $payment->fresh()->toArray(),
                meta: [
                    'order_id' => $lockedOrder->id,
                    'order_uuid' => $lockedOrder->uuid,
                    'provider' => $providerName,
                ],
            );

            return $payment->fresh('order.student');
        });
    }

    /**
     * @return array<string, mixed>
     */
    private function normalizeShippingSnapshot(Order $order, array $input): array
    {
        return [
            'recipient_name' => trim((string) ($input['recipient_name'] ?? $order->student?->name ?? '')),
            'phone' => trim((string) ($input['phone'] ?? $order->student?->phone ?? '')),
            'alternate_phone' => filled($input['alternate_phone'] ?? null) ? trim((string) $input['alternate_phone']) : null,
            'governorate' => trim((string) ($input['governorate'] ?? $order->student?->governorate ?? '')),
            'city' => trim((string) ($input['city'] ?? '')),
            'address_line1' => trim((string) ($input['address_line1'] ?? '')),
            'address_line2' => filled($input['address_line2'] ?? null) ? trim((string) $input['address_line2']) : null,
            'landmark' => filled($input['landmark'] ?? null) ? trim((string) $input['landmark']) : null,
        ];
    }
}
