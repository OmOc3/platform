<?php

namespace App\Modules\Commerce\Actions\Shipments;

use App\Modules\Commerce\Models\Order;
use App\Modules\Commerce\Models\Shipment;
use App\Shared\Contracts\AuditLogger;
use App\Shared\Enums\OrderKind;
use App\Shared\Enums\ShipmentStatus;
use Illuminate\Validation\ValidationException;

class PrepareShipmentForOrderAction
{
    public function __construct(private readonly AuditLogger $auditLogger)
    {
    }

    public function execute(Order $order, mixed $actor = null): Shipment
    {
        if ($order->kind !== OrderKind::Book) {
            throw ValidationException::withMessages([
                'order' => ['لا يمكن إنشاء شحنة لطلب رقمي.'],
            ]);
        }

        $snapshot = (array) data_get($order->meta, 'shipping_address', []);

        foreach (['recipient_name', 'phone', 'governorate', 'city', 'address_line1'] as $field) {
            if (! filled($snapshot[$field] ?? null)) {
                throw ValidationException::withMessages([
                    'shipping' => ['لا يمكن تجهيز الشحنة قبل استكمال عنوان الشحن وحفظه على الطلب.'],
                ]);
            }
        }

        $shipment = Shipment::query()->firstOrNew([
            'order_id' => $order->id,
        ]);

        $oldValues = $shipment->exists ? $shipment->toArray() : [];

        $shipment->fill([
            'status' => $shipment->status ?? ShipmentStatus::Pending,
            'recipient_name' => (string) $snapshot['recipient_name'],
            'phone' => (string) $snapshot['phone'],
            'alternate_phone' => $snapshot['alternate_phone'] ?? null,
            'governorate' => (string) $snapshot['governorate'],
            'city' => (string) $snapshot['city'],
            'address_line1' => (string) $snapshot['address_line1'],
            'address_line2' => $snapshot['address_line2'] ?? null,
            'landmark' => $snapshot['landmark'] ?? null,
            'shipping_fee_amount' => (int) data_get($order->meta, 'shipping_summary.amount', 0),
            'currency' => $order->currency,
            'meta' => array_filter([
                ...((array) $shipment->meta),
                'supported_governorates' => data_get($order->meta, 'shipping_summary.supported_governorates', []),
                'shipping_warning' => data_get($order->meta, 'shipping_summary.warning'),
            ], fn (mixed $value): bool => $value !== null),
        ]);

        $shipment->save();

        if ($oldValues === [] || $shipment->wasChanged()) {
            $this->auditLogger->log(
                event: $oldValues === [] ? 'commerce.shipment.created' : 'commerce.shipment.updated',
                actor: $actor,
                subject: $shipment,
                oldValues: $oldValues,
                newValues: $shipment->fresh()->toArray(),
                meta: [
                    'order_id' => $order->id,
                    'order_uuid' => $order->uuid,
                ],
            );
        }

        return $shipment->fresh();
    }
}
