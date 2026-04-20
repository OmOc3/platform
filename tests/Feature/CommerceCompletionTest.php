<?php

namespace Tests\Feature;

use App\Modules\Commerce\Models\Cart;
use App\Modules\Commerce\Models\CartItem;
use App\Modules\Commerce\Models\Entitlement;
use App\Modules\Commerce\Models\Order;
use App\Modules\Commerce\Models\Payment;
use App\Modules\Commerce\Models\PaymentWebhookReceipt;
use App\Modules\Commerce\Models\Product;
use App\Modules\Commerce\Models\Shipment;
use App\Modules\Students\Models\Student;
use App\Shared\Enums\OrderKind;
use App\Shared\Enums\OrderStatus;
use App\Shared\Enums\PaymentStatus;
use App\Shared\Enums\ShipmentStatus;
use App\Shared\Enums\StudentStatus;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\InteractsWithAdminAuth;
use Tests\TestCase;

class CommerceCompletionTest extends TestCase
{
    use InteractsWithAdminAuth;
    use RefreshDatabase;

    public function test_student_can_start_payment_for_each_checkout_order_and_reuse_active_attempt(): void
    {
        $this->seed(DatabaseSeeder::class);

        ['student' => $student, 'digitalOrder' => $digitalOrder, 'bookOrder' => $bookOrder] = $this->prepareCheckoutOrders();

        $this->actingAs($student, 'student');

        $this->post(route('student.checkout.orders.payments.store', $digitalOrder), [
            'provider' => 'fake',
        ])->assertRedirect();

        $digitalPayment = Payment::query()->where('order_id', $digitalOrder->id)->firstOrFail();

        $this->assertDatabaseHas('orders', [
            'id' => $digitalOrder->id,
            'status' => OrderStatus::PendingPayment->value,
        ]);

        $this->assertDatabaseHas('payments', [
            'id' => $digitalPayment->id,
            'order_id' => $digitalOrder->id,
            'status' => PaymentStatus::Pending->value,
        ]);

        $this->post(route('student.checkout.orders.payments.store', $digitalOrder->fresh()), [
            'provider' => 'fake',
        ])->assertRedirect();

        $this->assertSame(1, Payment::query()->where('order_id', $digitalOrder->id)->count());

        $this->post(route('student.checkout.orders.payments.store', $bookOrder), [
            'provider' => 'fake',
            'shipping' => $this->shippingPayload($student),
        ])->assertRedirect();

        $bookPayment = Payment::query()->where('order_id', $bookOrder->id)->firstOrFail();
        $bookOrder = $bookOrder->fresh();

        $this->assertDatabaseHas('orders', [
            'id' => $bookOrder->id,
            'status' => OrderStatus::PendingPayment->value,
        ]);

        $this->assertDatabaseHas('payments', [
            'id' => $bookPayment->id,
            'order_id' => $bookOrder->id,
            'status' => PaymentStatus::Pending->value,
        ]);

        $this->assertSame('القاهرة', data_get($bookOrder->meta, 'shipping_address.governorate'));
        $this->assertSame($bookOrder->total_amount, $bookPayment->amount);
        $this->assertSame(1, Payment::query()->where('order_id', $bookOrder->id)->count());
    }

    public function test_successful_digital_payment_webhook_is_idempotent_and_fulfills_order(): void
    {
        $this->seed(DatabaseSeeder::class);

        ['student' => $student, 'digitalOrder' => $digitalOrder] = $this->prepareCheckoutOrders();

        $this->actingAs($student, 'student');

        $this->post(route('student.checkout.orders.payments.store', $digitalOrder), [
            'provider' => 'fake',
        ])->assertRedirect();

        $payment = Payment::query()->where('order_id', $digitalOrder->id)->firstOrFail();

        $payload = [
            'event_key' => 'evt_digital_paid_1',
            'provider_reference' => $payment->provider_reference,
            'provider_transaction_reference' => 'txn_digital_paid_1',
            'status' => PaymentStatus::Paid->value,
            'note' => 'demo paid webhook',
        ];

        $this->postJson(route('payments.webhooks.handle', ['provider' => 'fake']), $payload)
            ->assertOk()
            ->assertJson(['processed' => true, 'payment_id' => $payment->id]);

        $payment = $payment->fresh();
        $digitalOrder = $digitalOrder->fresh('items.entitlement');

        $this->assertSame(PaymentStatus::Paid, $payment->status);
        $this->assertSame(OrderStatus::Fulfilled, $digitalOrder->status);
        $this->assertSame(1, Entitlement::query()->where('order_item_id', $digitalOrder->items->first()->id)->count());
        $this->assertSame(1, PaymentWebhookReceipt::query()->where('event_key', 'evt_digital_paid_1')->count());

        $this->postJson(route('payments.webhooks.handle', ['provider' => 'fake']), $payload)
            ->assertStatus(202)
            ->assertJson(['processed' => false, 'payment_id' => $payment->id]);

        $this->assertSame(1, Entitlement::query()->where('order_item_id', $digitalOrder->items->first()->id)->count());
        $this->assertSame(1, PaymentWebhookReceipt::query()->where('event_key', 'evt_digital_paid_1')->count());
    }

    public function test_failed_payment_webhook_marks_payment_failed_without_corrupting_order(): void
    {
        $this->seed(DatabaseSeeder::class);

        ['student' => $student, 'digitalOrder' => $digitalOrder] = $this->prepareCheckoutOrders();

        $this->actingAs($student, 'student');

        $this->post(route('student.checkout.orders.payments.store', $digitalOrder), [
            'provider' => 'fake',
        ])->assertRedirect();

        $payment = Payment::query()->where('order_id', $digitalOrder->id)->firstOrFail();

        $this->postJson(route('payments.webhooks.handle', ['provider' => 'fake']), [
            'event_key' => 'evt_digital_failed_1',
            'provider_reference' => $payment->provider_reference,
            'provider_transaction_reference' => 'txn_digital_failed_1',
            'status' => PaymentStatus::Failed->value,
            'note' => 'demo failed webhook',
        ])->assertOk();

        $this->assertSame(PaymentStatus::Failed, $payment->fresh()->status);
        $this->assertSame(OrderStatus::PendingPayment, $digitalOrder->fresh()->status);
        $this->assertDatabaseMissing('entitlements', [
            'order_item_id' => $digitalOrder->items()->first()->id,
        ]);
    }

    public function test_successful_book_payment_prepares_shipment_and_admin_can_advance_it(): void
    {
        $this->seed(DatabaseSeeder::class);

        ['student' => $student, 'bookOrder' => $bookOrder] = $this->prepareCheckoutOrders();

        $this->actingAs($student, 'student');

        $this->post(route('student.checkout.orders.payments.store', $bookOrder), [
            'provider' => 'fake',
            'shipping' => $this->shippingPayload($student),
        ])->assertRedirect();

        $payment = Payment::query()->where('order_id', $bookOrder->id)->firstOrFail();

        $this->postJson(route('payments.webhooks.handle', ['provider' => 'fake']), [
            'event_key' => 'evt_book_paid_1',
            'provider_reference' => $payment->provider_reference,
            'provider_transaction_reference' => 'txn_book_paid_1',
            'status' => PaymentStatus::Paid->value,
            'note' => 'demo paid webhook',
        ])->assertOk();

        $bookOrder = $bookOrder->fresh('shipment');
        $shipment = $bookOrder->shipment;

        $this->assertNotNull($shipment);
        $this->assertSame(OrderStatus::ReadyForShipping, $bookOrder->status);
        $this->assertSame(ShipmentStatus::Pending, $shipment->status);

        $this->signInAdmin(['shipping.view', 'shipping.manage']);

        $this->get(route('admin.shipments.show', $shipment))
            ->assertOk()
            ->assertSeeText($shipment->recipient_name);

        $this->put(route('admin.shipments.update', $shipment), [
            'status' => ShipmentStatus::Prepared->value,
            'carrier_name' => 'Aramex Demo',
        ])->assertRedirect(route('admin.shipments.show', $shipment));

        $this->assertSame(OrderStatus::ReadyForShipping, $bookOrder->fresh()->status);

        $this->put(route('admin.shipments.update', $shipment->fresh()), [
            'status' => ShipmentStatus::InTransit->value,
            'carrier_name' => 'Aramex Demo',
            'carrier_reference' => 'SHIP-1001',
        ])->assertRedirect(route('admin.shipments.show', $shipment));

        $this->assertSame(OrderStatus::Shipped, $bookOrder->fresh()->status);

        $this->put(route('admin.shipments.update', $shipment->fresh()), [
            'status' => ShipmentStatus::Delivered->value,
            'carrier_name' => 'Aramex Demo',
            'carrier_reference' => 'SHIP-1001',
        ])->assertRedirect(route('admin.shipments.show', $shipment));

        $this->assertSame(OrderStatus::Completed, $bookOrder->fresh()->status);
    }

    public function test_admin_permissions_gate_payment_refunds_and_shipment_updates(): void
    {
        $this->seed(DatabaseSeeder::class);

        ['payment' => $payment, 'shipment' => $shipment] = $this->createPaidBookOrderFlow();

        $this->signInAdmin(['transactions.view', 'shipping.view']);

        $this->put(route('admin.payments.refund', $payment), [
            'reason' => 'no permission',
        ])->assertForbidden();

        $this->put(route('admin.shipments.update', $shipment), [
            'status' => ShipmentStatus::Prepared->value,
        ])->assertForbidden();
    }

    public function test_invalid_shipment_transition_and_refund_guard_are_enforced(): void
    {
        $this->seed(DatabaseSeeder::class);

        ['payment' => $payment, 'shipment' => $shipment] = $this->createPaidBookOrderFlow();

        $this->signInAdmin(['transactions.view', 'transactions.manage', 'shipping.view', 'shipping.manage']);

        $this->from(route('admin.shipments.show', $shipment))
            ->put(route('admin.shipments.update', $shipment), [
                'status' => ShipmentStatus::Delivered->value,
            ])
            ->assertRedirect(route('admin.shipments.show', $shipment))
            ->assertSessionHasErrors('status');

        $failedPayment = Payment::factory()->create([
            'status' => PaymentStatus::Failed,
        ]);

        $this->from(route('admin.payments.show', $failedPayment))
            ->put(route('admin.payments.refund', $failedPayment), [
                'reason' => 'invalid refund',
            ])
            ->assertRedirect(route('admin.payments.show', $failedPayment))
            ->assertSessionHasErrors('payment');
    }

    public function test_admin_with_transactions_view_can_list_payments(): void
    {
        $this->seed(DatabaseSeeder::class);

        ['payment' => $payment] = $this->createPaidBookOrderFlow();

        $this->signInAdmin(['transactions.view']);

        $this->get(route('admin.payments.index'))
            ->assertOk()
            ->assertSeeText($payment->provider_reference);
    }

    /**
     * @return array{student: Student, digitalOrder: Order, bookOrder: Order}
     */
    private function prepareCheckoutOrders(): array
    {
        $student = Student::factory()->create([
            'status' => StudentStatus::Subscribed,
            'phone' => '01012345679',
            'parent_phone' => '01012345678',
            'governorate' => 'القاهرة',
        ]);

        $cart = Cart::factory()->create([
            'student_id' => $student->id,
            'currency' => 'EGP',
        ]);

        $digitalProduct = Product::query()->where('slug', 'accelerated-motion-problem-solving')->firstOrFail();
        $bookProduct = Product::query()->where('slug', 'smart-solutions-book')->firstOrFail();

        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $digitalProduct->id,
            'quantity' => 1,
            'unit_price_amount' => $digitalProduct->price_amount,
            'total_price_amount' => $digitalProduct->price_amount,
            'meta' => ['product_kind' => $digitalProduct->kind->value],
        ]);

        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $bookProduct->id,
            'quantity' => 1,
            'unit_price_amount' => $bookProduct->price_amount,
            'total_price_amount' => $bookProduct->price_amount,
            'meta' => ['product_kind' => $bookProduct->kind->value],
        ]);

        $this->actingAs($student, 'student');

        $this->post(route('student.checkout.prepare'))
            ->assertRedirect(route('student.checkout.show'));

        return [
            'student' => $student,
            'digitalOrder' => Order::query()
                ->where('student_id', $student->id)
                ->where('kind', OrderKind::Digital->value)
                ->where('status', OrderStatus::Draft->value)
                ->latest('id')
                ->firstOrFail(),
            'bookOrder' => Order::query()
                ->where('student_id', $student->id)
                ->where('kind', OrderKind::Book->value)
                ->where('status', OrderStatus::Draft->value)
                ->latest('id')
                ->firstOrFail(),
        ];
    }

    /**
     * @return array{payment: Payment, shipment: Shipment}
     */
    private function createPaidBookOrderFlow(): array
    {
        ['student' => $student, 'bookOrder' => $bookOrder] = $this->prepareCheckoutOrders();

        $this->actingAs($student, 'student');

        $this->post(route('student.checkout.orders.payments.store', $bookOrder), [
            'provider' => 'fake',
            'shipping' => $this->shippingPayload($student),
        ])->assertRedirect();

        $payment = Payment::query()->where('order_id', $bookOrder->id)->firstOrFail();

        $this->postJson(route('payments.webhooks.handle', ['provider' => 'fake']), [
            'event_key' => 'evt_book_paid_flow_'.$bookOrder->id,
            'provider_reference' => $payment->provider_reference,
            'provider_transaction_reference' => 'txn_book_paid_'.$bookOrder->id,
            'status' => PaymentStatus::Paid->value,
            'note' => 'seeded paid flow',
        ])->assertOk();

        return [
            'payment' => $payment->fresh(),
            'shipment' => Shipment::query()->where('order_id', $bookOrder->id)->firstOrFail(),
        ];
    }

    /**
     * @return array<string, string>
     */
    private function shippingPayload(Student $student): array
    {
        return [
            'recipient_name' => $student->name,
            'phone' => $student->phone,
            'alternate_phone' => $student->parent_phone,
            'governorate' => 'القاهرة',
            'city' => 'مدينة نصر',
            'address_line1' => 'شارع عباس العقاد',
            'address_line2' => 'الدور الثالث',
            'landmark' => 'بجوار الحديقة الدولية',
        ];
    }
}
