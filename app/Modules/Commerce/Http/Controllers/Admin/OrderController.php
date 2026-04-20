<?php

namespace App\Modules\Commerce\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Commerce\Actions\Orders\TransitionOrderAction;
use App\Modules\Commerce\Http\Requests\Admin\Orders\TransitionOrderRequest;
use App\Modules\Commerce\Models\Entitlement;
use App\Modules\Commerce\Models\Order;
use App\Modules\Commerce\Queries\OrdersIndexQuery;
use App\Shared\Enums\OrderKind;
use App\Shared\Enums\OrderStatus;
use App\Shared\Support\Exports\CsvExporter;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OrderController extends Controller
{
    public function __construct(
        private readonly OrdersIndexQuery $ordersIndexQuery,
        private readonly TransitionOrderAction $transitionOrderAction,
    ) {
    }

    public function index(Request $request): View|StreamedResponse
    {
        $this->authorize('viewAny', Order::class);

        $query = $this->ordersIndexQuery->builder($request);

        if ($request->string('export')->toString() === 'csv') {
            return CsvExporter::download('orders.csv', ['الرقم', 'الطالب', 'النوع', 'الحالة', 'الإجمالي'], $query->get()
                ->map(fn (Order $order): array => [
                    $order->uuid,
                    $order->student?->name ?? '-',
                    $order->kind->label(),
                    $order->status->labelFor($order->kind),
                    (string) $order->total_amount,
                ])
                ->all());
        }

        return view('admin.commerce.orders.index', [
            'orders' => $query->paginate(15)->withQueryString(),
            'statuses' => OrderStatus::cases(),
            'kinds' => OrderKind::cases(),
            'overview' => [
                'total' => Order::query()->count(),
                'book' => Order::query()->where('kind', OrderKind::Book->value)->count(),
                'digital' => Order::query()->where('kind', OrderKind::Digital->value)->count(),
                'actionable' => Order::query()->whereIn('status', [OrderStatus::PendingPayment->value, OrderStatus::Paid->value])->count(),
            ],
        ]);
    }

    public function show(Order $order): View
    {
        $this->authorize('view', $order);

        $order->load([
            'student.center',
            'student.group',
            'items.product.package.items.item',
            'items.entitlement.product',
            'items.entitlement.grantedByAdmin',
        ]);

        return view('admin.commerce.orders.show', [
            'order' => $order,
            'availableTransitions' => $this->transitionOrderAction->availableTransitions($order),
            'canViewEntitlements' => auth('admin')->user()?->can('viewAny', Entitlement::class) ?? false,
        ]);
    }

    public function transition(TransitionOrderRequest $request, Order $order): RedirectResponse
    {
        $this->authorize('update', $order);

        $result = $this->transitionOrderAction->execute(
            $order,
            OrderStatus::from($request->validated('status')),
            auth('admin')->user(),
        );

        return redirect()
            ->route('admin.orders.show', $result['order'])
            ->with('status', $result['message']);
    }
}
