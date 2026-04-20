<x-layouts.admin title="الكتب" heading="الكتب" subheading="إدارة كتالوج الكتب الورقية وربط الجاهزية التجارية بالمخزون وحالة الإتاحة.">
    <section class="admin-metric-grid">
        <x-admin.metric-card label="إجمالي الكتب" :value="$overview['total']" description="كل الكتب المعرفة داخل الكتالوج." />
        <x-admin.metric-card label="متاح الآن" :value="$overview['available']" description="كتب جاهزة للإضافة إلى سلة الطالب الآن." />
        <x-admin.metric-card label="كتب مميزة" :value="$overview['featured']" description="العناصر البارزة على واجهات الطلاب." />
        <x-admin.metric-card label="إجمالي المخزون" :value="$overview['stock']" description="مجموع النسخ المسجلة حاليًا داخل النظام." />
    </section>

    <x-admin.table-shell title="الكتب" description="بحث سريع حسب الاسم أو حالة التوفر، مع إبراز الكتب الأكثر تأثيرًا على واجهة الطالب.">
        <x-slot:actions>
            <a href="{{ route('admin.books.create') }}" class="btn-primary">إضافة كتاب</a>
            <a href="{{ route('admin.books.index', array_merge(request()->query(), ['export' => 'csv'])) }}" class="btn-secondary">تصدير CSV</a>
        </x-slot:actions>

        <x-slot:filters>
            <form method="GET" class="grid gap-3 md:grid-cols-[1fr_220px_auto]">
                <input type="search" name="search" value="{{ request('search') }}" class="form-input" placeholder="ابحث باسم الكتاب أو الرابط المختصر">
                <select name="availability" class="form-select">
                    <option value="">كل الحالات</option>
                    @foreach (\App\Modules\Commerce\Enums\BookAvailability::cases() as $availability)
                        <option value="{{ $availability->value }}" @selected(request('availability') === $availability->value)>{{ $availability->label() }}</option>
                    @endforeach
                </select>
                <button class="btn-secondary">تطبيق</button>
            </form>
        </x-slot:filters>

        <table class="data-table">
            <thead>
                <tr>
                    <th>الكتاب</th>
                    <th>التسعير</th>
                    <th>المخزون</th>
                    <th>الحالة</th>
                    <th>إجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($books as $row)
                    <tr>
                        <td>
                            <div class="flex items-start gap-3">
                                <div class="catalog-thumb !min-h-20 !w-20 shrink-0 rounded-[1.4rem]">
                                    @if ($row->product?->thumbnail_url)
                                        <img src="{{ $row->product->thumbnail_url }}" alt="{{ $row->product?->name_ar }}">
                                    @else
                                        <div class="catalog-thumb__fallback !min-h-20 !p-3">
                                            <span>{{ $row->cover_badge ?: 'كتاب' }}</span>
                                            <strong class="!text-base">{{ $row->page_count ?: '—' }} ص</strong>
                                        </div>
                                    @endif
                                </div>
                                <div>
                                    <p class="font-semibold">{{ $row->product?->name_ar }}</p>
                                    <p class="mt-1 text-xs text-[var(--color-ink-500)]">{{ $row->author_name ?: 'بدون مؤلف محدد' }}</p>
                                    <div class="mt-2 flex flex-wrap gap-2">
                                        @if ($row->cover_badge)
                                            <x-admin.status-badge :label="$row->cover_badge" />
                                        @endif
                                        <x-admin.status-badge :label="$row->product?->is_active ? 'ظاهر للطلاب' : 'موقوف'" :tone="$row->product?->is_active ? 'success' : 'warning'" />
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <p class="font-semibold">{{ number_format($row->product?->price_amount ?? 0) }} {{ $row->product?->currency }}</p>
                            <p class="mt-1 text-xs text-[var(--color-ink-500)]">{{ $row->product?->slug }}</p>
                        </td>
                        <td>
                            <p class="font-semibold">{{ $row->stock_quantity }}</p>
                            <p class="mt-1 text-xs text-[var(--color-ink-500)]">عدد الصفحات: {{ $row->page_count ?: '—' }}</p>
                        </td>
                        <td>
                            <x-admin.status-badge :label="$row->availability_status->label()" :tone="$row->availability_status->tone()" />
                        </td>
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
                    <tr><td colspan="5" class="text-center text-[var(--color-ink-500)]">لا توجد كتب مطابقة للفلاتر الحالية.</td></tr>
                @endforelse
            </tbody>
        </table>

        <div class="px-5 py-4">{{ $books->links() }}</div>
    </x-admin.table-shell>
</x-layouts.admin>
