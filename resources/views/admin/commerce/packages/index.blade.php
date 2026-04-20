<x-layouts.admin title="الباقات" heading="الباقات" subheading="إدارة العروض المجمعة وربطها بالمحاضرات وسياسات التداخل والوصول.">
    <section class="admin-metric-grid">
        <x-admin.metric-card label="إجمالي الباقات" :value="$overview['total']" description="كل العروض الرقمية المعرفة داخل النظام." />
        <x-admin.metric-card label="باقات مميزة" :value="$overview['featured']" description="العروض التي تأخذ أولوية على واجهة الطالب." />
        <x-admin.metric-card label="عروض شهرية" :value="$overview['monthly']" description="باقات تحمل نمطًا شهريًا في دورة الوصول." />
        <x-admin.metric-card label="عناصر مرتبطة" :value="$overview['items']" description="عدد الروابط الحالية بين الباقات والمحاضرات." />
    </section>

    <x-admin.table-shell title="الباقات" description="تحكم في الباقات الشهرية والخاصة وعدد العناصر المضمنة في كل عرض.">
        <x-slot:actions>
            <a href="{{ route('admin.packages.create') }}" class="btn-primary">إضافة باقة</a>
            <a href="{{ route('admin.packages.index', array_merge(request()->query(), ['export' => 'csv'])) }}" class="btn-secondary">تصدير CSV</a>
        </x-slot:actions>

        <x-slot:filters>
            <form method="GET" class="grid gap-3 md:grid-cols-[1fr_220px_auto]">
                <input type="search" name="search" value="{{ request('search') }}" class="form-input" placeholder="ابحث باسم الباقة أو الرابط المختصر" aria-label="ابحث في الباقات">
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
                    <th>المحتوى</th>
                    <th>السعر</th>
                    <th>إجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($packages as $row)
                    <tr>
                        <td>
                            <div class="flex items-start gap-3">
                                <div class="catalog-thumb !min-h-20 !w-20 shrink-0 rounded-[1.4rem]">
                                    @if ($row->product?->thumbnail_url)
                            <img src="{{ $row->product->thumbnail_url }}" alt="{{ $row->product?->name_ar }}" loading="lazy" decoding="async">
                                    @else
                                        <div class="catalog-thumb__fallback !min-h-20 !p-3">
                                            <span>{{ $row->billing_cycle_label ?: 'باقة' }}</span>
                                            <strong class="!text-base">{{ $row->lecture_count }} عنصر</strong>
                                        </div>
                                    @endif
                                </div>
                                <div>
                                    <p class="font-semibold">{{ $row->product?->name_ar }}</p>
                                    <p class="mt-1 text-xs text-[var(--color-ink-500)]">{{ $row->product?->teaser ?: 'بدون وصف مختصر' }}</p>
                                    <div class="mt-2 flex flex-wrap gap-2">
                                        @if ($row->is_featured)
                                            <x-admin.status-badge label="مميزة" tone="success" />
                                        @endif
                                        <x-admin.status-badge :label="$row->product?->is_active ? 'منشورة' : 'موقوفة'" :tone="$row->product?->is_active ? 'success' : 'warning'" />
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <p class="font-semibold">{{ $row->billing_cycle_label ?: 'غير محددة' }}</p>
                            <p class="mt-1 text-xs text-[var(--color-ink-500)]">{{ $row->access_period_days ? $row->access_period_days.' يوم' : 'مدة مفتوحة' }}</p>
                        </td>
                        <td>
                            <p class="font-semibold">{{ $row->items->count() }} عنصر</p>
                            <p class="mt-1 text-xs text-[var(--color-ink-500)]">سياسة التداخل: {{ data_get($row->metadata, 'overlap_rule') === 'allow' ? 'سماح مع تنبيه' : 'منع التداخل' }}</p>
                        </td>
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
                    <tr><td colspan="5" class="text-center text-[var(--color-ink-500)]">لا توجد باقات مطابقة للفلاتر الحالية.</td></tr>
                @endforelse
            </tbody>
        </table>

        <div class="px-5 py-4">{{ $packages->links() }}</div>
    </x-admin.table-shell>
</x-layouts.admin>
