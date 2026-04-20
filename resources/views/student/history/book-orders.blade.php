<x-layouts.student title="مدفوعات الكتب" heading="طلبات الكتب" subheading="متابعة طلبات الكتب، حالة الدفع، وحالة الشحن لكل طلب تم تسجيله على حسابك.">
    <section class="space-y-6">
        <x-student.account-nav current="book-orders" />

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <x-student.summary-card label="إجمالي الطلبات" :value="$summary['count']" description="كل طلبات الكتب المسجلة" />
            <x-student.summary-card label="مكتملة" :value="$summary['fulfilled_count']" description="طلبات تم تسليمها أو إغلاقها بنجاح" />
            <x-student.summary-card label="قيد المتابعة" :value="$summary['pending_count']" description="طلبات ما زالت في دورة الدفع أو الشحن" />
            <x-student.summary-card label="إجمالي القيمة" :value="number_format($summary['total_amount']).' ج'" description="إجمالي قيمة الطلبات المسجلة" />
        </section>

        <section class="table-shell">
            <div class="flex flex-col gap-3 px-5 py-5 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="text-sm font-semibold text-[var(--color-brand-700)]">سجل الطلبات</p>
                    <h2 class="mt-2 text-xl font-bold">طلبات الكتب والشحن</h2>
                </div>
                <a href="{{ route('student.books.index') }}" class="btn-secondary">العودة إلى الكتب</a>
            </div>

            @if ($orders->isEmpty())
                <div class="px-5 pb-5">
                    <x-student.empty-state title="لا توجد طلبات كتب" description="عند تنفيذ أي طلب كتب سيظهر هنا مع تفاصيل حالة الدفع والشحن." />
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
                                <th>الدفع</th>
                                <th>الشحن</th>
                                <th>الحالة</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($orders as $order)
                                @php($payment = $order->payments->first())
                                <tr>
                                    <td class="font-semibold">{{ $order->uuid }}</td>
                                    <td>
                                        <div class="flex flex-col gap-1">
                                            @foreach ($order->items as $item)
                                                <span>{{ $item->product_name_snapshot }} × {{ $item->quantity }}</span>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td>
                                        <div class="flex flex-col gap-1">
                                            <span>{{ optional($order->placed_at)->format('Y-m-d') ?: '—' }}</span>
                                            <span class="text-xs text-[var(--color-ink-500)]">{{ optional($order->placed_at)->format('H:i') ?: '—' }}</span>
                                        </div>
                                    </td>
                                    <td>{{ number_format($order->total_amount) }} {{ $order->currency }}</td>
                                    <td>
                                        @if ($payment)
                                            <x-admin.status-badge :label="$payment->status->label()" :tone="$payment->status->tone()" />
                                        @else
                                            <span class="text-sm text-[var(--color-ink-500)]">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($order->shipment)
                                            <x-admin.status-badge :label="$order->shipment->status->label()" :tone="$order->shipment->status->tone()" />
                                        @else
                                            <span class="text-sm text-[var(--color-ink-500)]">لم تُجهز بعد</span>
                                        @endif
                                    </td>
                                    <td><x-admin.status-badge :label="$order->status->labelFor($order->kind)" :tone="$order->status->tone()" /></td>
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
    </section>
</x-layouts.student>
