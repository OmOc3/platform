<x-layouts.student title="طلبات الكتب" heading="طلبات الكتب" subheading="عرض الطلبات السابقة وما تحتويه من كتب وحالة تنفيذها.">
    <section class="table-shell">
        <div class="px-5 py-5">
            <h2 class="text-lg font-bold">سجل طلبات الكتب</h2>
        </div>

        @if ($orders->isEmpty())
            <div class="px-5 pb-5">
                <x-student.empty-state title="لا توجد طلبات كتب" description="عند تنفيذ أي طلب كتاب سيظهر هنا مع تفاصيل العنصر وحالة الطلب." />
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>الطلب</th>
                            <th>العناصر</th>
                            <th>التاريخ</th>
                            <th>الإجمالي</th>
                            <th>الحالة</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($orders as $order)
                            <tr>
                                <td class="font-semibold">{{ $order->uuid }}</td>
                                <td>{{ $order->items->pluck('product_name_snapshot')->implode('، ') }}</td>
                                <td>{{ optional($order->placed_at)->format('Y-m-d') ?: '—' }}</td>
                                <td>{{ number_format($order->total_amount) }} {{ $order->currency }}</td>
                                <td><x-admin.status-badge :label="$order->status->value" tone="success" /></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="px-5 py-4">
                {{ $orders->links() }}
            </div>
        @endif
    </section>
</x-layouts.student>
