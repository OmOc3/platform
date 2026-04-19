<?php

namespace App\Shared\Services;

use App\Modules\Commerce\Models\Entitlement;
use App\Modules\Commerce\Models\Order;
use App\Modules\Commerce\Models\OrderItem;
use App\Modules\Commerce\Models\Product;
use App\Shared\Contracts\AuditLogger;
use App\Shared\Contracts\EntitlementGrantor;
use App\Shared\Enums\EntitlementSource;
use App\Shared\Enums\OrderKind;
use App\Shared\Enums\ProductKind;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Carbon;

class DatabaseEntitlementGrantor implements EntitlementGrantor
{
    public function __construct(private readonly AuditLogger $auditLogger)
    {
    }

    /**
     * @return Collection<int, Entitlement>
     */
    public function grant(array $payload): mixed
    {
        /** @var Order $order */
        $order = $payload['order'];
        $actor = $payload['actor'] ?? null;
        $grantedAt = $payload['granted_at'] ?? now();
        $shouldAudit = (bool) ($payload['audit'] ?? true);

        if (! $order->relationLoaded('items')) {
            $order->load('items.product.package');
        }

        if ($order->kind !== OrderKind::Digital) {
            return collect();
        }

        return $order->items
            ->map(fn (OrderItem $item): ?Entitlement => $this->grantForOrderItem($order, $item, $actor, $grantedAt, $shouldAudit))
            ->filter()
            ->values();
    }

    private function grantForOrderItem(
        Order $order,
        OrderItem $item,
        mixed $actor,
        CarbonInterface|string $grantedAt,
        bool $shouldAudit,
    ): ?Entitlement {
        $product = $item->product;

        if (! $product instanceof Product || $product->kind === ProductKind::Book) {
            return null;
        }

        $grantedAtValue = $grantedAt instanceof CarbonInterface ? $grantedAt->copy() : Carbon::parse($grantedAt);
        $source = $this->resolveSource($product);
        $packageDays = $product->kind === ProductKind::Package ? $product->package?->access_period_days : null;
        $defaultEndsAt = $packageDays ? $grantedAtValue->copy()->addDays($packageDays) : null;

        $entitlement = Entitlement::query()->firstOrNew([
            'order_item_id' => $item->id,
        ]);

        $oldValues = $entitlement->exists ? $entitlement->toArray() : [];

        $entitlement->fill([
            'student_id' => $order->student_id,
            'product_id' => $product->id,
            'source' => $source,
            'status' => 'active',
            'item_name_snapshot' => $item->product_name_snapshot ?: $product->name_ar,
            'price_amount' => $item->total_price_amount,
            'currency' => $order->currency ?: $product->currency ?: 'EGP',
            'granted_by_admin_id' => null,
            'meta' => array_filter([
                ...((array) $entitlement->meta),
                'fulfilled_order_id' => $order->id,
                'fulfilled_order_uuid' => $order->uuid,
                'product_kind' => $product->kind->value,
            ], fn (mixed $value): bool => $value !== null),
        ]);

        $entitlement->granted_at ??= $grantedAtValue;
        $entitlement->starts_at ??= $grantedAtValue;
        $entitlement->ends_at ??= $defaultEndsAt;

        $isDirty = $entitlement->isDirty();
        $entitlement->save();

        if ($shouldAudit && ($oldValues === [] || $isDirty)) {
            $this->auditLogger->log(
                event: $oldValues === [] ? 'commerce.entitlement.granted' : 'commerce.entitlement.updated',
                actor: $actor,
                subject: $entitlement,
                oldValues: $oldValues,
                newValues: $entitlement->fresh()->toArray(),
                meta: [
                    'order_id' => $order->id,
                    'order_uuid' => $order->uuid,
                    'order_item_id' => $item->id,
                    'source' => $source->value,
                ],
            );
        }

        return $entitlement;
    }

    private function resolveSource(Product $product): EntitlementSource
    {
        return match ($product->kind) {
            ProductKind::Package => EntitlementSource::PackagePurchase,
            default => EntitlementSource::DirectPurchase,
        };
    }
}
