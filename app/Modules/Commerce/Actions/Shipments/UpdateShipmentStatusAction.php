<?php

namespace App\Modules\Commerce\Actions\Shipments;

use App\Modules\Commerce\Actions\Orders\TransitionOrderAction;
use App\Modules\Commerce\Models\Shipment;
use App\Shared\Contracts\AuditLogger;
use App\Shared\Enums\OrderStatus;
use App\Shared\Enums\ShipmentStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UpdateShipmentStatusAction
{
    public function __construct(
        private readonly TransitionOrderAction $transitionOrderAction,
        private readonly AuditLogger $auditLogger,
    ) {
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array{shipment: Shipment, changed: bool}
     */
    public function execute(Shipment $shipment, ShipmentStatus $targetStatus, mixed $actor, array $payload = []): array
    {
        return DB::transaction(function () use ($shipment, $targetStatus, $actor, $payload): array {
            /** @var Shipment $lockedShipment */
            $lockedShipment = Shipment::query()
                ->with('order.items.product.package')
                ->lockForUpdate()
                ->findOrFail($shipment->id);

            if ($lockedShipment->status === $targetStatus) {
                return [
                    'shipment' => $lockedShipment->fresh('order'),
                    'changed' => false,
                ];
            }

            if (! in_array($targetStatus, $this->availableTransitions($lockedShipment), true)) {
                throw ValidationException::withMessages([
                    'status' => ['لا يمكن نقل الشحنة إلى هذه الحالة من وضعها الحالي.'],
                ]);
            }

            $oldValues = $lockedShipment->toArray();

            $lockedShipment->status = $targetStatus;
            $lockedShipment->carrier_name = $payload['carrier_name'] ?? $lockedShipment->carrier_name;
            $lockedShipment->carrier_reference = $payload['carrier_reference'] ?? $lockedShipment->carrier_reference;
            $lockedShipment->meta = array_merge((array) $lockedShipment->meta, array_filter([
                'notes' => $payload['notes'] ?? null,
            ], fn (mixed $value): bool => $value !== null));

            match ($targetStatus) {
                ShipmentStatus::Prepared => $lockedShipment->prepared_at ??= now(),
                ShipmentStatus::HandedToCarrier => $lockedShipment->handed_to_carrier_at ??= now(),
                ShipmentStatus::InTransit => $lockedShipment->in_transit_at ??= now(),
                ShipmentStatus::Delivered => $lockedShipment->delivered_at ??= now(),
                ShipmentStatus::Returned => $lockedShipment->returned_at ??= now(),
                ShipmentStatus::Canceled => $lockedShipment->canceled_at ??= now(),
                default => null,
            };

            $lockedShipment->save();

            $orderTargetStatus = match ($targetStatus) {
                ShipmentStatus::Pending, ShipmentStatus::Prepared => OrderStatus::ReadyForShipping,
                ShipmentStatus::HandedToCarrier, ShipmentStatus::InTransit => OrderStatus::Shipped,
                ShipmentStatus::Delivered => OrderStatus::Completed,
                ShipmentStatus::Returned, ShipmentStatus::Canceled => OrderStatus::Cancelled,
            };

            if ($lockedShipment->order->status !== $orderTargetStatus) {
                $this->transitionOrderAction->execute($lockedShipment->order, $orderTargetStatus, $actor);
            }

            $this->auditLogger->log(
                event: 'commerce.shipment.status_changed',
                actor: $actor,
                subject: $lockedShipment,
                oldValues: $oldValues,
                newValues: $lockedShipment->fresh()->toArray(),
                meta: [
                    'order_id' => $lockedShipment->order_id,
                    'to_status' => $targetStatus->value,
                ],
            );

            return [
                'shipment' => $lockedShipment->fresh('order'),
                'changed' => true,
            ];
        });
    }

    /**
     * @return list<ShipmentStatus>
     */
    public function availableTransitions(Shipment $shipment): array
    {
        return match ($shipment->status) {
            ShipmentStatus::Pending => [ShipmentStatus::Prepared, ShipmentStatus::Canceled],
            ShipmentStatus::Prepared => [ShipmentStatus::HandedToCarrier, ShipmentStatus::InTransit, ShipmentStatus::Canceled],
            ShipmentStatus::HandedToCarrier => [ShipmentStatus::InTransit, ShipmentStatus::Delivered, ShipmentStatus::Returned],
            ShipmentStatus::InTransit => [ShipmentStatus::Delivered, ShipmentStatus::Returned],
            default => [],
        };
    }
}
