<?php

namespace App\Modules\Commerce\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Modules\Commerce\Actions\Payments\HandlePaymentWebhookAction;
use App\Modules\Commerce\Actions\Payments\StartOrderPaymentAction;
use App\Modules\Commerce\Http\Requests\Student\CompleteFakePaymentRequest;
use App\Modules\Commerce\Http\Requests\Student\StartOrderPaymentRequest;
use App\Modules\Commerce\Models\Order;
use App\Modules\Commerce\Models\Payment;
use App\Shared\Enums\OrderKind;
use App\Shared\Enums\PaymentStatus;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

class OrderPaymentController extends Controller
{
    public function __construct(
        private readonly StartOrderPaymentAction $startOrderPaymentAction,
        private readonly HandlePaymentWebhookAction $handlePaymentWebhookAction,
    ) {
    }

    public function store(StartOrderPaymentRequest $request, Order $order): RedirectResponse
    {
        abort_unless($order->student_id === auth('student')->id(), 403);

        $payment = $this->startOrderPaymentAction->execute($order, $request->validated(), auth('student')->user());

        return redirect($payment->checkout_url ?: route('student.checkout.show'))
            ->with('status', 'تم تجهيز عملية الدفع الحالية.');
    }

    public function showFakeCheckout(Payment $payment): View
    {
        abort_unless($payment->order?->student_id === auth('student')->id(), 403);

        return view('student.cart.fake-payment', [
            'payment' => $payment->load(['order.items.product', 'order.shipment']),
            'statuses' => [
                PaymentStatus::Paid,
                PaymentStatus::Failed,
                PaymentStatus::Canceled,
            ],
        ]);
    }

    public function completeFakeCheckout(CompleteFakePaymentRequest $request, Payment $payment): RedirectResponse
    {
        abort_unless($payment->order?->student_id === auth('student')->id(), 403);

        $status = PaymentStatus::from($request->validated('status'));

        $this->handlePaymentWebhookAction->execute('fake', [
            'event_key' => 'fake_checkout_'.Str::uuid(),
            'provider_reference' => $payment->provider_reference,
            'provider_transaction_reference' => 'txn_'.Str::lower(Str::random(16)),
            'status' => $status->value,
            'meta' => [
                'note' => 'تمت محاكاة الاستجابة من بوابة الدفع التجريبية.',
                'trigger' => 'fake_checkout',
            ],
            'payload' => [
                'payment_id' => $payment->id,
                'status' => $status->value,
            ],
        ], auth('student')->user());

        $freshOrder = $payment->fresh('order')->order;

        if ($status === PaymentStatus::Paid) {
            return redirect()
                ->route($freshOrder->kind === OrderKind::Digital ? 'student.payments.index' : 'student.book-orders.index')
                ->with('status', 'تم تأكيد السداد وتحديث الطلب.');
        }

        return redirect()
            ->route('student.checkout.show')
            ->with('status', $status === PaymentStatus::Failed
                ? 'تم تسجيل فشل عملية السداد الحالية، ويمكنك إعادة المحاولة.'
                : 'تم إلغاء عملية الدفع الحالية.');
    }
}
