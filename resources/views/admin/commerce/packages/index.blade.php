<x-layouts.admin title="الباقات" heading="الباقات" subheading="إدارة الباقات الرقمية وربطها بالمحاضرات مع سياسات التعارض الجاهزة للتوسع.">
    <x-admin.table-shell title="الباقات" description="عرض الباقات وسرعة الانتقال للتعديل أو الإضافة أو التصدير.">
        <x-slot:actions>
            <a href="{{ route('admin.packages.create') }}" class="btn-primary">إضافة باقة</a>
            <a href="{{ route('admin.packages.index', array_merge(request()->query(), ['export' => 'csv'])) }}" class="btn-secondary">تصدير CSV</a>
        </x-slot:actions>

        <x-slot:filters>
            <form method="GET" class="grid gap-3 md:grid-cols-[1fr_220px_auto]">
                <input type="search" name="search" value="{{ request('search') }}" class="form-input" placeholder="ابحث باسم الباقة">
                <select name="featured" class="form-select">
                    <option value="">كل الباقات</option>
                    <option value="1" @selected(request('featured') === '1')>مميزة فقط</option>
                    <option value="0" @selected(request('featured') === '0')>غير مميزة</option>
                </select>
                <button class="btn-secondary">تطبيق</button>
            </form>
        </x-slot:filters>

        <table class="data-table">
            <thead>
                <tr>
                    <th>الباقة</th>
                    <th>الدورة</th>
                    <th>العناصر</th>
                    <th>السعر</th>
                    <th>إجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($packages as $row)
                    <tr>
                        <td class="font-semibold">{{ $row->product?->name_ar }}</td>
                        <td>{{ $row->billing_cycle_label ?? '—' }}</td>
                        <td>{{ $row->items->count() }}</td>
                        <td>{{ number_format($row->product?->price_amount ?? 0) }} {{ $row->product?->currency }}</td>
                        <td>
                            <div class="flex flex-wrap gap-2">
                                <a href="{{ route('admin.packages.edit', $row) }}" class="btn-secondary !px-4 !py-2">تعديل</a>
                                <form method="POST" action="{{ route('admin.packages.destroy', $row) }}" onsubmit="return confirm('تأكيد حذف الباقة؟');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn-danger">حذف</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-[var(--color-ink-500)]">لا توجد باقات بعد.</td></tr>
                @endforelse
            </tbody>
        </table>

        <div class="px-5 py-4">{{ $packages->links() }}</div>
    </x-admin.table-shell>
</x-layouts.admin>
