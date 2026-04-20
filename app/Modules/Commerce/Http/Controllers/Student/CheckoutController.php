<?php

namespace App\Modules\Commerce\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Modules\Commerce\Actions\Cart\PrepareCheckoutAction;
use App\Modules\Commerce\Models\Order;
use App\Modules\Commerce\Queries\CartSummaryQuery;
use App\Shared\Enums\OrderKind;
use App\Shared\Enums\OrderStatus;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly CartSummaryQuery $cartSummaryQuery,
        private readonly PrepareCheckoutAction $prepareCheckoutAction,
    ) {
    }

    public function show(): View
    {
        $student = auth('student')->user();

        return view('student.cart.checkout', [
            'student' => $student,
            ...$this->cartSummaryQuery->dataFor($student),
            'digitalOrder' => Order::query()
                ->with(['items.product', 'payments' => fn ($query) => $query->latest('created_at')])
                ->where('student_id', $student->id)
                ->where('kind', OrderKind::Digital->value)
                ->whereIn('status', [OrderStatus::Draft->value, OrderStatus::PendingPayment->value])
                ->latest('updated_at')
                ->first(),
            'bookOrder' => Order::query()
                ->with(['items.product', 'payments' => fn ($query) => $query->latest('created_at')])
                ->where('student_id', $student->id)
                ->where('kind', OrderKind::Book->value)
                ->whereIn('status', [OrderStatus::Draft->value, OrderStatus::PendingPayment->value])
                ->latest('updated_at')
                ->first(),
        ]);
    }

    public function prepare(): RedirectResponse
    {
        $orders = $this->prepareCheckoutAction->execute(auth('student')->user());

        return redirect()
            ->route('student.checkout.show')
            ->with([
                'status' => 'تم تجهيز مسودة الطلبات الحالية.',
                'checkout_orders' => [
                    'digital' => $orders['digitalOrder']?->id,
                    'book' => $orders['bookOrder']?->id,
                ],
            ]);
    }
}
