<x-layouts.admin title="الكتب" heading="الكتب" subheading="إدارة كتالوج الكتب الورقية مع المخزون وحالات الإتاحة والتسعير.">
    <x-admin.table-shell title="الكتب" description="عرض الكتب الحالية والبحث عنها بحسب الاسم أو حالة التوفر.">
        <x-slot:actions>
            <a href="{{ route('admin.books.create') }}" class="btn-primary">إضافة كتاب</a>
            <a href="{{ route('admin.books.index', array_merge(request()->query(), ['export' => 'csv'])) }}" class="btn-secondary">تصدير CSV</a>
        </x-slot:actions>

        <x-slot:filters>
            <form method="GET" class="grid gap-3 md:grid-cols-[1fr_220px_auto]">
                <input type="search" name="search" value="{{ request('search') }}" class="form-input" placeholder="ابحث باسم الكتاب">
                <select name="availability" class="form-select">
                    <option value="">كل الحالات</option>
                    <option value="in_stock" @selected(request('availability') === 'in_stock')>متوفر</option>
                    <option value="pre_order" @selected(request('availability') === 'pre_order')>طلب مسبق</option>
                    <option value="sold_out" @selected(request('availability') === 'sold_out')>نفد</option>
                </select>
                <button class="btn-secondary">تطبيق</button>
            </form>
        </x-slot:filters>

        <table class="data-table">
            <thead>
                <tr>
                    <th>الكتاب</th>
                    <th>المؤلف</th>
                    <th>المخزون</th>
                    <th>الحالة</th>
                    <th>إجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($books as $row)
                    <tr>
                        <td class="font-semibold">{{ $row->product?->name_ar }}</td>
                        <td>{{ $row->author_name ?? '—' }}</td>
                        <td>{{ $row->stock_quantity }}</td>
                        <td><x-admin.status-badge :label="$row->availability_status->value" :tone="$row->availability_status->value === 'in_stock' ? 'success' : ($row->availability_status->value === 'pre_order' ? 'warning' : 'danger')" /></td>
                        <td>
                            <div class="flex flex-wrap gap-2">
                                <a href="{{ route('admin.books.edit', $row) }}" class="btn-secondary !px-4 !py-2">تعديل</a>
                                <form method="POST" action="{{ route('admin.books.destroy', $row) }}" onsubmit="return confirm('تأكيد حذف الكتاب؟');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn-danger">حذف</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-[var(--color-ink-500)]">لا توجد كتب بعد.</td></tr>
                @endforelse
            </tbody>
        </table>

        <div class="px-5 py-4">{{ $books->links() }}</div>
    </x-admin.table-shell>
</x-layouts.admin>
