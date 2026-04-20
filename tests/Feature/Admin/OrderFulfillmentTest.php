<?php

namespace Tests\Feature\Admin;

use App\Modules\Academic\Models\Lecture;
use App\Modules\Commerce\Models\Book;
use App\Modules\Commerce\Models\Entitlement;
use App\Modules\Commerce\Models\Order;
use App\Modules\Commerce\Models\OrderItem;
use App\Modules\Commerce\Models\Package;
use App\Modules\Commerce\Models\Product;
use App\Modules\Students\Models\Student;
use App\Shared\Contracts\AccessResolver;
use App\Shared\Enums\ContentAccessState;
use App\Shared\Enums\EntitlementSource;
use App\Shared\Enums\OrderKind;
use App\Shared\Enums\OrderStatus;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\InteractsWithAdminAuth;
use Tests\TestCase;

class OrderFulfillmentTest extends TestCase
{
    use InteractsWithAdminAuth;
    use RefreshDatabase;

    public function test_admin_with_orders_view_can_list_orders(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->signInAdmin(['orders.view']);

        $this->get(route('admin.orders.index'))
            ->assertOk()
            ->assertSeeText('digital-order-demo-package-100002');
    }

    public function test_admin_without_orders_manage_cannot_transition_order(): void
    {
        $this->seed(DatabaseSeeder::class);

        $lecture = $this->directLecture();
        $student = Student::factory()->create();
        $order = $this->createOrder($student, $lecture->product, OrderKind::Digital, OrderStatus::Paid, 140);

        $this->signInAdmin(['orders.view']);

        $this->put(route('admin.orders.transition', $order), [
            'status' => OrderStatus::Fulfilled->value,
        ])->assertForbidden();
    }

    public function test_fulfilling_paid_digital_order_creates_direct_entitlement_and_access(): void
    {
        $this->seed(DatabaseSeeder::class);

        $lecture = $this->directLecture();
        $student = Student::factory()->create();
        $order = $this->createOrder($student, $lecture->product, OrderKind::Digital, OrderStatus::Paid, 140);

        /** @var AccessResolver $accessResolver */
        $accessResolver = app(AccessResolver::class);

        $this->assertSame(ContentAccessState::Buy, $accessResolver->resolveState($student, $lecture)['state']);

        $this->signInAdmin(['orders.view', 'orders.manage', 'entitlements.view']);

        $this->put(route('admin.orders.transition', $order), [
            'status' => OrderStatus::Fulfilled->value,
        ])->assertRedirect(route('admin.orders.show', $order));

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => OrderStatus::Fulfilled->value,
        ]);

        $this->assertDatabaseHas('entitlements', [
            'student_id' => $student->id,
            'product_id' => $lecture->product->id,
            'order_item_id' => $order->items()->first()->id,
            'source' => EntitlementSource::DirectPurchase->value,
        ]);

        $this->assertSame(ContentAccessState::OwnedViaEntitlement, $accessResolver->resolveState($student, $lecture)['state']);

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'commerce.order.transitioned',
            'auditable_id' => $order->id,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'commerce.entitlement.granted',
        ]);
    }

    public function test_fulfilling_same_order_twice_is_idempotent(): void
    {
        $this->seed(DatabaseSeeder::class);

        $lecture = $this->directLecture();
        $student = Student::factory()->create();
        $order = $this->createOrder($student, $lecture->product, OrderKind::Digital, OrderStatus::Paid, 140);

        $this->signInAdmin(['orders.view', 'orders.manage']);

        $this->put(route('admin.orders.transition', $order), [
            'status' => OrderStatus::Fulfilled->value,
        ])->assertRedirect();

        $this->put(route('admin.orders.transition', $order->fresh()), [
            'status' => OrderStatus::Fulfilled->value,
        ])->assertRedirect();

        $this->assertSame(1, Entitlement::query()->where('order_item_id', $order->items()->first()->id)->count());
    }

    public function test_fulfilling_package_order_creates_package_entitlement_and_unlocks_included_lecture(): void
    {
        $this->seed(DatabaseSeeder::class);

        $package = Package::query()
            ->with(['product', 'items.item'])
            ->whereHas('product', fn ($query) => $query->where('slug', 'monthly-physics-package'))
            ->firstOrFail();
        $lecture = $package->items->first()?->item;
        $student = Student::factory()->create();
        $order = $this->createOrder($student, $package->product, OrderKind::Digital, OrderStatus::Paid, $package->product->price_amount);

        /** @var AccessResolver $accessResolver */
        $accessResolver = app(AccessResolver::class);

        $this->assertSame(ContentAccessState::IncludedInPackage, $accessResolver->resolveState($student, $lecture)['state']);

        $this->signInAdmin(['orders.view', 'orders.manage']);

        $this->put(route('admin.orders.transition', $order), [
            'status' => OrderStatus::Fulfilled->value,
        ])->assertRedirect();

        $this->assertDatabaseHas('entitlements', [
            'student_id' => $student->id,
            'product_id' => $package->product->id,
            'source' => EntitlementSource::PackagePurchase->value,
        ]);

        $this->assertSame(ContentAccessState::OwnedViaEntitlement, $accessResolver->resolveState($student, $lecture)['state']);
    }

    public function test_preparing_paid_book_order_for_shipping_does_not_create_digital_entitlements(): void
    {
        $this->seed(DatabaseSeeder::class);

        $book = Book::query()->with('product')->firstOrFail();
        $student = Student::factory()->create();
        $order = $this->createOrder(
            $student,
            $book->product,
            OrderKind::Book,
            OrderStatus::Paid,
            $book->product->price_amount,
            [
                'shipping_address' => [
                    'recipient_name' => $student->name,
                    'phone' => $student->phone,
                    'alternate_phone' => $student->parent_phone,
                    'governorate' => 'القاهرة',
                    'city' => 'مدينة نصر',
                    'address_line1' => 'شارع عباس العقاد',
                ],
                'shipping_summary' => [
                    'amount' => 35,
                    'label' => 'رسوم شحن تقديرية',
                    'warning' => null,
                    'supported_governorates' => ['القاهرة', 'الجيزة', 'الإسكندرية'],
                ],
            ],
        );

        $this->signInAdmin(['orders.view', 'orders.manage']);

        $this->put(route('admin.orders.transition', $order), [
            'status' => OrderStatus::ReadyForShipping->value,
        ])->assertRedirect();

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => OrderStatus::ReadyForShipping->value,
        ]);

        $this->assertDatabaseHas('shipments', [
            'order_id' => $order->id,
            'status' => 'pending',
        ]);

        $this->assertDatabaseMissing('entitlements', [
            'order_item_id' => $order->items()->first()->id,
        ]);
    }

    public function test_invalid_transition_is_rejected_without_mutating_order(): void
    {
        $this->seed(DatabaseSeeder::class);

        $lecture = $this->directLecture();
        $student = Student::factory()->create();
        $order = $this->createOrder($student, $lecture->product, OrderKind::Digital, OrderStatus::Draft, 140);

        $this->signInAdmin(['orders.view', 'orders.manage']);

        $this->from(route('admin.orders.show', $order))
            ->put(route('admin.orders.transition', $order), [
                'status' => OrderStatus::Fulfilled->value,
            ])
            ->assertRedirect(route('admin.orders.show', $order))
            ->assertSessionHasErrors('status');

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => OrderStatus::Draft->value,
        ]);

        $this->assertDatabaseMissing('audit_logs', [
            'event' => 'commerce.order.transitioned',
            'auditable_id' => $order->id,
        ]);
    }

    private function createOrder(
        Student $student,
        Product $product,
        OrderKind $kind,
        OrderStatus $status,
        int $priceAmount,
        ?array $meta = null,
    ): Order {
        $order = Order::factory()->create([
            'student_id' => $student->id,
            'kind' => $kind,
            'status' => $status,
            'subtotal_amount' => $priceAmount,
            'total_amount' => $priceAmount,
            'currency' => $product->currency,
            'meta' => $meta,
        ]);

        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_kind' => $product->kind,
            'product_name_snapshot' => $product->name_ar,
            'quantity' => 1,
            'unit_price_amount' => $priceAmount,
            'total_price_amount' => $priceAmount,
        ]);

        return $order->fresh('items');
    }

    private function directLecture(): Lecture
    {
        return Lecture::factory()->create()->fresh('product');
    }
}
