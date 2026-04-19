<?php

namespace Tests\Feature;

use App\Modules\Academic\Models\Lecture;
use App\Modules\Commerce\Models\Book;
use App\Modules\Commerce\Models\CartItem;
use App\Modules\Commerce\Models\Order;
use App\Modules\Commerce\Models\Package;
use App\Modules\Commerce\Models\Product;
use App\Modules\Students\Models\Student;
use App\Shared\Enums\OrderKind;
use App\Shared\Enums\OrderStatus;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentCatalogAndCommerceTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_browse_real_catalog_pages_and_package_states(): void
    {
        $this->seed(DatabaseSeeder::class);

        $student = Student::query()->firstWhere('email', 'student@example.edu');
        $freeLecture = Lecture::query()->firstWhere('slug', 'foundation-kinematics-free');
        $includedLecture = Lecture::query()->firstWhere('slug', 'accelerated-motion-problem-solving');
        $blockedPackage = Package::query()->whereHas('product', fn ($query) => $query->where('slug', 'monthly-physics-package'))->firstOrFail();
        $allowedPackage = Package::query()->whereHas('product', fn ($query) => $query->where('slug', 'intensive-camp-package'))->firstOrFail();
        $book = Book::query()->with('product')->firstOrFail();

        $this->actingAs($student, 'student');

        $this->get(route('student.lectures.index', ['tab' => 'lecture']))
            ->assertOk()
            ->assertSeeText('قوانين نيوتن الأساسية');

        $this->get(route('student.lectures.index', ['tab' => 'review']))
            ->assertOk()
            ->assertSeeText('مراجعة أساسيات الكهرباء');

        $this->get(route('student.lectures.index', ['tab' => 'exam']))
            ->assertOk()
            ->assertSeeText('اختبار قوانين نيوتن الأسبوعي');

        $this->get(route('student.lectures.show', $freeLecture))
            ->assertOk()
            ->assertSeeText('افتح المحتوى');

        $this->get(route('student.lectures.show', $includedLecture))
            ->assertOk()
            ->assertSeeText('استعرض الباقات المرتبطة');

        $this->get(route('student.packages.index'))
            ->assertOk()
            ->assertSeeText('باقة الفيزياء الشهرية');

        $this->get(route('student.packages.show', $blockedPackage))
            ->assertOk()
            ->assertSeeText('الشراء غير متاح حاليًا')
            ->assertSeeText('قوانين نيوتن الأساسية');

        $this->get(route('student.packages.show', $allowedPackage))
            ->assertOk()
            ->assertSeeText('أضف الباقة إلى السلة');

        $this->get(route('student.books.index'))
            ->assertOk()
            ->assertSeeText($book->product->name_ar);

        $this->get(route('student.books.show', $book))
            ->assertOk()
            ->assertSeeText('أضف الكتاب إلى السلة');
    }

    public function test_student_can_manage_cart_and_prepare_separate_draft_orders(): void
    {
        $this->seed(DatabaseSeeder::class);

        $student = Student::query()->firstWhere('email', 'student@example.edu');
        $bookItem = CartItem::query()
            ->whereHas('cart', fn ($query) => $query->where('student_id', $student->id))
            ->whereHas('product', fn ($query) => $query->where('kind', 'book'))
            ->firstOrFail();
        $digitalItem = CartItem::query()
            ->whereHas('cart', fn ($query) => $query->where('student_id', $student->id))
            ->whereHas('product', fn ($query) => $query->where('kind', 'package'))
            ->firstOrFail();
        $digitalProduct = Product::query()->findOrFail($digitalItem->product_id);

        $this->actingAs($student, 'student');

        $this->put(route('student.cart.update', $bookItem), [
            'quantity' => 3,
        ])->assertRedirect(route('student.cart.index'));

        $this->assertDatabaseHas('cart_items', [
            'id' => $bookItem->id,
            'quantity' => 3,
        ]);

        $this->put(route('student.cart.update', $digitalItem), [
            'quantity' => 4,
        ])->assertRedirect(route('student.cart.index'));

        $this->assertDatabaseHas('cart_items', [
            'id' => $digitalItem->id,
            'quantity' => 1,
        ]);

        $this->delete(route('student.cart.destroy', $digitalItem))
            ->assertRedirect(route('student.cart.index'));

        $this->assertDatabaseMissing('cart_items', [
            'id' => $digitalItem->id,
        ]);

        $this->post(route('student.cart.store'), [
            'product_id' => $digitalProduct->id,
            'quantity' => 5,
        ])->assertRedirect();

        $restoredDigitalItem = CartItem::query()
            ->whereHas('cart', fn ($query) => $query->where('student_id', $student->id))
            ->where('product_id', $digitalProduct->id)
            ->firstOrFail();

        $this->assertSame(1, $restoredDigitalItem->quantity);

        $this->post(route('student.checkout.prepare'))
            ->assertRedirect(route('student.checkout.show'));

        $digitalOrder = Order::query()
            ->where('student_id', $student->id)
            ->where('kind', OrderKind::Digital->value)
            ->where('status', OrderStatus::Draft->value)
            ->first();

        $bookOrder = Order::query()
            ->where('student_id', $student->id)
            ->where('kind', OrderKind::Book->value)
            ->where('status', OrderStatus::Draft->value)
            ->first();

        $this->assertNotNull($digitalOrder);
        $this->assertNotNull($bookOrder);
        $this->assertCount(1, $digitalOrder->items);
        $this->assertCount(1, $bookOrder->items);

        $this->get(route('student.checkout.show'))
            ->assertOk()
            ->assertSeeText('مسودة الطلب الرقمي')
            ->assertSeeText('مسودة طلب الكتب');
    }
}
