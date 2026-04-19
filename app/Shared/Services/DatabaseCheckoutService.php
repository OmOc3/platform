<?php

namespace App\Shared\Services;

use App\Modules\Commerce\Models\Order;
use App\Modules\Commerce\Models\OrderItem;
use App\Modules\Students\Models\Student;
use App\Shared\Contracts\CheckoutService;
use App\Shared\Enums\OrderKind;
use App\Shared\Enums\OrderStatus;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class DatabaseCheckoutService implements CheckoutService
{
    public function beginDigitalCheckout(array $payload): mixed
    {
        return $this->syncDraftOrder($payload, OrderKind::Digital);
    }

    public function beginBookCheckout(array $payload): mixed
    {
        return $this->syncDraftOrder($payload, OrderKind::Book);
    }

    private function syncDraftOrder(array $payload, OrderKind $kind): ?Order
    {
        /** @var Student $student */
        $student = $payload['student'];
        /** @var Collection<int, \App\Modules\Commerce\Models\CartItem> $items */
        $items = $payload['items'];

        if ($items->isEmpty()) {
            Order::query()
                ->where('student_id', $student->id)
                ->where('kind', $kind->value)
                ->where('status', OrderStatus::Draft->value)
                ->delete();

            return null;
        }

        $subtotal = $items->sum('total_price_amount');
        $currency = $items->first()?->product?->currency ?? 'EGP';

        $order = Order::query()->firstOrCreate(
            [
                'student_id' => $student->id,
                'kind' => $kind->value,
                'status' => OrderStatus::Draft->value,
            ],
            [
                'uuid' => (string) Str::uuid(),
                'subtotal_amount' => 0,
                'total_amount' => 0,
                'currency' => $currency,
                'placed_at' => null,
            ],
        );

        $order->update([
            'subtotal_amount' => $subtotal,
            'total_amount' => $subtotal,
            'currency' => $currency,
        ]);

        $order->items()->delete();

        foreach ($items as $item) {
            OrderItem::query()->create([
                'order_id' => $order->id,
                'product_id' => $item->product_id,
                'product_kind' => $item->product?->kind,
                'product_name_snapshot' => $item->product?->name_ar ?? 'عنصر',
                'quantity' => $item->quantity,
                'unit_price_amount' => $item->unit_price_amount,
                'total_price_amount' => $item->total_price_amount,
                'meta' => [
                    'prepared_from_cart_item_id' => $item->id,
                ],
            ]);
        }

        return $order->fresh('items.product');
    }
}
