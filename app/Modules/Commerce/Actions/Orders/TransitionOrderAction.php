<?php

namespace App\Modules\Commerce\Actions\Orders;

use App\Modules\Commerce\Models\Order;
use App\Shared\Contracts\AuditLogger;
use App\Shared\Contracts\EntitlementGrantor;
use App\Shared\Enums\OrderKind;
use App\Shared\Enums\OrderStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TransitionOrderAction
{
    public function __construct(
        private readonly EntitlementGrantor $entitlementGrantor,
        private readonly AuditLogger $auditLogger,
    ) {
    }

    /**
     * @return array{order: Order, changed: bool, message: string, granted_count: int}
     */
    public function execute(Order $order, OrderStatus $targetStatus, mixed $actor): array
    {
        return DB::transaction(function () use ($order, $targetStatus, $actor): array {
            /** @var Order $lockedOrder */
            $lockedOrder = Order::query()
                ->with(['student', 'items.product.package', 'items.entitlement'])
                ->lockForUpdate()
                ->findOrFail($order->id);

            if ($lockedOrder->status === $targetStatus) {
                return [
                    'order' => $lockedOrder->fresh(['student', 'items.product.package', 'items.entitlement']),
                    'changed' => false,
                    'message' => 'الطلب بالفعل في الحالة المطلوبة.',
                    'granted_count' => $lockedOrder->kind === OrderKind::Digital
                        ? $lockedOrder->items->filter(fn ($item): bool => $item->entitlement !== null)->count()
                        : 0,
                ];
            }

            if (! in_array($targetStatus, $this->availableTransitions($lockedOrder), true)) {
                throw ValidationException::withMessages([
                    'status' => ['لا يمكن نقل الطلب إلى هذه الحالة من وضعه الحالي.'],
                ]);
            }

            $oldValues = $lockedOrder->toArray();
            $grantedCount = 0;

            $lockedOrder->status = $targetStatus;

            if (in_array($targetStatus, [OrderStatus::PendingPayment, OrderStatus::Paid, OrderStatus::Fulfilled], true) && ! $lockedOrder->placed_at) {
                $lockedOrder->placed_at = now();
            }

            $lockedOrder->save();

            if ($lockedOrder->kind === OrderKind::Digital && $targetStatus === OrderStatus::Fulfilled) {
                $grantedCount = collect($this->entitlementGrantor->grant([
                    'order' => $lockedOrder,
                    'actor' => $actor,
                    'granted_at' => now(),
                ]))->count();
            }

            $freshOrder = $lockedOrder->fresh(['student', 'items.product.package', 'items.entitlement']);

            $this->auditLogger->log(
                event: 'commerce.order.transitioned',
                actor: $actor,
                subject: $freshOrder,
                oldValues: $oldValues,
                newValues: $freshOrder->toArray(),
                meta: [
                    'from_status' => $oldValues['status'] ?? null,
                    'to_status' => $targetStatus->value,
                    'granted_entitlements' => $grantedCount,
                ],
            );

            return [
                'order' => $freshOrder,
                'changed' => true,
                'message' => $targetStatus === OrderStatus::Fulfilled
                    ? 'تم تفعيل الطلب وإضافة الاستحقاقات الرقمية المرتبطة به.'
                    : 'تم تحديث حالة الطلب.',
                'granted_count' => $grantedCount,
            ];
        });
    }

    /**
     * @return list<OrderStatus>
     */
    public function availableTransitions(Order $order): array
    {
        return match ($order->status) {
            OrderStatus::Draft => [OrderStatus::PendingPayment],
            OrderStatus::PendingPayment => [OrderStatus::Paid, OrderStatus::Cancelled],
            OrderStatus::Paid => [OrderStatus::Fulfilled, OrderStatus::Cancelled],
            default => [],
        };
    }
}
