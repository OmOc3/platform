<x-layouts.admin title="الطلبات" heading="الطلبات" subheading="متابعة الطلبات الرقمية وطلبات الكتب مع التحكم في الانتقالات الآمنة للحالة والتفعيل.">
    <x-admin.table-shell title="قائمة الطلبات" description="يمكنك البحث عن الطالب أو رقم الطلب، مع تصفية النوع والحالة، ثم الانتقال إلى صفحة التفاصيل لاتخاذ إجراء.">
        <x-slot:actions>
            <a href="{{ route('admin.orders.index', array_merge(request()->query(), ['export' => 'csv'])) }}" class="btn-secondary">تصدير CSV</a>
        </x-slot:actions>

        <x-slot:filters>
            <form method="GET" class="grid gap-3 md:grid-cols-[1fr_220px_220px_auto]">
                <input type="search" name="search" value="{{ request('search') }}" class="form-input" placeholder="ابحث باسم الطالب أو البريد أو رقم الطلب">

                <select name="kind" class="form-select">
                    <option value="">كل الأنواع</option>
                    @foreach ($kinds as $kind)
                        <option value="{{ $kind->value }}" @selected(request('kind') === $kind->value)>{{ $kind->label() }}</option>
                    @endforeach
                </select>

                <select name="status" class="form-select">
                    <option value="">كل الحالات</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
                    @endforeach
                </select>

                <button class="btn-secondary">تطبيق</button>
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
                        <td><x-admin.status-badge :label="$order->status->label()" :tone="$order->status->tone()" /></td>
                        <td>{{ number_format($order->total_amount) }} {{ $order->currency }}</td>
                        <td>{{ $order->items->count() }}</td>
                        <td>{{ optional($order->created_at)->format('Y-m-d H:i') }}</td>
                        <td>
                            <a href="{{ route('admin.orders.show', $order) }}" class="btn-secondary !px-4 !py-2">التفاصيل</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-[var(--color-ink-500)]">لا توجد طلبات مطابقة للبحث الحالي.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="px-5 py-4">{{ $orders->links() }}</div>
    </x-admin.table-shell>
</x-layouts.admin>
