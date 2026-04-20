<?php

namespace App\Modules\Commerce\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Commerce\Actions\Payments\RefundPaymentAction;
use App\Modules\Commerce\Http\Requests\Admin\Payments\RefundPaymentRequest;
use App\Modules\Commerce\Models\Payment;
use App\Modules\Commerce\Queries\PaymentsIndexQuery;
use App\Shared\Enums\PaymentStatus;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(
        private readonly PaymentsIndexQuery $paymentsIndexQuery,
        private readonly RefundPaymentAction $refundPaymentAction,
    ) {
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Payment::class);

        return view('admin.commerce.payments.index', [
            'payments' => $this->paymentsIndexQuery->builder($request)->paginate(15)->withQueryString(),
            'statuses' => PaymentStatus::cases(),
            'overview' => [
                'total' => Payment::query()->count(),
                'pending' => Payment::query()->whereIn('status', [PaymentStatus::Pending->value, PaymentStatus::RequiresAction->value])->count(),
                'paid' => Payment::query()->where('status', PaymentStatus::Paid->value)->count(),
                'failed' => Payment::query()->where('status', PaymentStatus::Failed->value)->count(),
            ],
        ]);
    }

    public function show(Payment $payment): View
    {
        $this->authorize('view', $payment);

        return view('admin.commerce.payments.show', [
            'payment' => $payment->load(['order.student', 'order.items.product', 'webhookReceipts']),
        ]);
    }

    public function refund(RefundPaymentRequest $request, Payment $payment): RedirectResponse
    {
        $this->authorize('update', $payment);

        $result = $this->refundPaymentAction->execute($payment, auth('admin')->user(), [
            'source' => 'admin',
            'reason' => $request->validated('reason'),
        ]);

        return redirect()
            ->route('admin.payments.show', $result['payment'])
            ->with('status', 'تم تسجيل عملية الارتجاع على المدفوعة والطلب المرتبط بها.');
    }
}
