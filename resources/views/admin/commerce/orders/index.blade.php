<x-layouts.admin title="الطلبات" heading="الطلبات" subheading="متابعة الطلبات الرقمية وطلبات الكتب مع إبراز الطلبات التي ما زالت تحتاج قرارًا من الإدارة.">
    <section class="admin-metric-grid">
        <x-admin.metric-card label="كل الطلبات" :value="$overview['total']" description="إجمالي الطلبات داخل المنصة." />
        <x-admin.metric-card label="طلبات الكتب" :value="$overview['book']" description="طلبات تحتاج متابعة شحن أو تسليم." />
        <x-admin.metric-card label="طلبات رقمية" :value="$overview['digital']" description="طلبات ينتج عنها وصول أو استحقاقات رقمية." />
        <x-admin.metric-card label="تحتاج إجراء" :value="$overview['actionable']" description="طلبات في انتظار التأكيد أو التفعيل." />
    </section>

    <x-admin.table-shell title="قائمة الطلبات" description="ابحث باسم الطالب أو رقم الطلب، ثم انتقل إلى صفحة التفاصيل لمراجعة العناصر وحالة التنفيذ.">
        <x-slot:actions>
            <a href="{{ route('admin.orders.index', array_merge(request()->query(), ['export' => 'csv'])) }}" class="btn-secondary">تصدير CSV</a>
        </x-slot:actions>

        <x-slot:filters>
            <form method="GET" class="grid gap-3 md:grid-cols-[1fr_220px_220px_auto]">
                <div>
                    <label class="field-label" for="orders_search">ابحث في الطلبات</label>
                    <input id="orders_search" type="search" name="search" value="{{ request('search') }}" class="form-input" placeholder="اسم الطالب، البريد، أو رقم الطلب">
                </div>

                <div>
                    <label class="field-label" for="orders_kind">نوع الطلب</label>
                    <select id="orders_kind" name="kind" class="form-select">
                        <option value="">كل الأنواع</option>
                        @foreach ($kinds as $kind)
                            <option value="{{ $kind->value }}" @selected(request('kind') === $kind->value)>{{ $kind->label() }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="field-label" for="orders_status">حالة الطلب</label>
                    <select id="orders_status" name="status" class="form-select">
                        <option value="">كل الحالات</option>
                        @foreach ($statuses as $status)
                            <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
                        @endforeach
                    </select>
                </div>

                <button class="btn-secondary md:self-end">تطبيق الفلاتر</button>
            </form>
        </x-slot:filters>

        <table class="data-table">
            <thead>
                <tr>
                    <th>رقم الطلب</th>
                    <th>الطالب</th>
                    <th>النوع</th>
                    <th>الحالة</th>
                    <th>الإجمالي</th>
                    <th>العناصر</th>
                    <th>تاريخ الإنشاء</th>
                    <th>إجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($orders as $order)
                    <tr>
                        <td class="font-mono text-xs">{{ $order->uuid }}</td>
                        <td>
                            <p class="font-semibold">{{ $order->student?->name ?? '—' }}</p>
                            <p class="text-xs text-[var(--color-ink-500)]">{{ $order->student?->student_number ?? '—' }}</p>
                        </td>
                        <td>{{ $order->kind->label() }}</td>
                        <td><x-admin.status-badge :label="$order->status->labelFor($order->kind)" :tone="$order->status->tone()" /></td>
                        <td>{{ number_format($order->total_amount) }} {{ $order->currency }}</td>
                        <td>{{ $order->items->count() }}</td>
                        <td>{{ optional($order->created_at)->format('Y-m-d H:i') }}</td>
                        <td>
                            <a href="{{ route('admin.orders.show', $order) }}" class="btn-secondary !px-4 !py-2">التفاصيل</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-[var(--color-ink-500)]">لا توجد طلبات مطابقة للفلاتر الحالية.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="px-5 py-4">{{ $orders->links() }}</div>
    </x-admin.table-shell>
</x-layouts.admin>
