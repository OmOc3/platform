<?php

namespace App\Modules\Commerce\Actions\Orders;

use App\Modules\Commerce\Actions\Shipments\PrepareShipmentForOrderAction;
use App\Modules\Commerce\Models\Order;
use App\Modules\Commerce\Models\Shipment;
use App\Shared\Enums\OrderKind;
use App\Shared\Enums\OrderStatus;
use Illuminate\Validation\ValidationException;

class FinalizePaidOrderAction
{
    public function __construct(
        private readonly TransitionOrderAction $transitionOrderAction,
        private readonly PrepareShipmentForOrderAction $prepareShipmentForOrderAction,
    ) {
    }

    /**
     * @return array{order: Order, shipment: ?Shipment, changed: bool}
     */
    public function execute(Order $order, mixed $actor = null): array
    {
        $order->loadMissing(['items.product.package', 'shipment']);

        if (! in_array($order->status, [OrderStatus::Paid, OrderStatus::Fulfilled, OrderStatus::ReadyForShipping], true)) {
            throw ValidationException::withMessages([
                'order' => ['لا يمكن إتمام هذا الطلب من حالته الحالية.'],
            ]);
        }

        if ($order->kind === OrderKind::Digital) {
            if ($order->status === OrderStatus::Fulfilled) {
                return [
                    'order' => $order->fresh(['items.entitlement', 'payments']),
                    'shipment' => null,
                    'changed' => false,
                ];
            }

            $result = $this->transitionOrderAction->execute($order, OrderStatus::Fulfilled, $actor);

            return [
                'order' => $result['order'],
                'shipment' => null,
                'changed' => $result['changed'],
            ];
        }

        $shipment = $this->prepareShipmentForOrderAction->execute($order, $actor);

        if ($order->status === OrderStatus::ReadyForShipping) {
            return [
                'order' => $order->fresh(['shipment', 'payments']),
                'shipment' => $shipment,
                'changed' => false,
            ];
        }

        $result = $this->transitionOrderAction->execute($order, OrderStatus::ReadyForShipping, $actor);

        return [
            'order' => $result['order']->fresh(['shipment', 'payments']),
            'shipment' => $shipment,
            'changed' => $result['changed'],
        ];
    }
}
