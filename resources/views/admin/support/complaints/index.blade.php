<x-layouts.admin title="الشكاوى والاقتراحات" heading="الشكاوى والاقتراحات" subheading="قناة متابعة موحدة لرسائل الطلاب التنظيمية والملاحظات التحسينية.">
    <section class="admin-metric-grid">
        <x-admin.metric-card label="كل الرسائل" :value="$overview['total']" description="إجمالي الشكاوى والاقتراحات المسجلة." />
        <x-admin.metric-card label="تحتاج متابعة" :value="$overview['open']" description="رسائل مفتوحة أو قيد المتابعة الآن." />
        <x-admin.metric-card label="تم حلها" :value="$overview['resolved']" description="رسائل أغلقت بعد معالجة واضحة." />
        <x-admin.metric-card label="اقتراحات" :value="$overview['suggestions']" description="رسائل تحسين وتجربة استخدام من الطلاب." />
    </section>

    <x-admin.table-shell title="سجل الشكاوى والاقتراحات" description="ابحث باسم الطالب أو محتوى الرسالة، ثم افتح العنصر لإضافة ملاحظات أو تحديث الحالة.">
        <x-slot:actions>
            <a href="{{ route('admin.complaints.index', array_merge(request()->query(), ['export' => 'csv'])) }}" class="btn-secondary">تصدير CSV</a>
        </x-slot:actions>

        <x-slot:filters>
            <form method="GET" class="grid gap-3 md:grid-cols-[1fr_220px_220px_auto]">
                <input type="search" name="search" value="{{ request('search') }}" class="form-input" placeholder="ابحث باسم الطالب أو نص الرسالة" aria-label="ابحث في الشكاوى والاقتراحات">
                <select name="type" class="form-select">
                    <option value="">كل الأنواع</option>
                    @foreach ($types as $type)
                        <option value="{{ $type->value }}" @selected(request('type') === $type->value)>{{ $type->label() }}</option>
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
                    <th>الرسالة</th>
                    <th>الطالب</th>
                    <th>النوع</th>
                    <th>الحالة</th>
                    <th>التاريخ</th>
                    <th>إجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($complaints as $complaint)
                    <tr>
                        <td>
                            <p class="font-semibold">{{ str($complaint->content)->limit(90) }}</p>
                            @if ($complaint->admin_notes)
                                <p class="mt-2 text-xs text-[var(--color-ink-500)]">ملاحظة الإدارة: {{ str($complaint->admin_notes)->limit(70) }}</p>
                            @endif
                        </td>
                        <td>
                            <p class="font-semibold">{{ $complaint->student?->name ?? '—' }}</p>
                            <p class="mt-1 text-xs text-[var(--color-ink-500)]">{{ $complaint->student?->student_number ?? '—' }}</p>
                        </td>
                        <td>{{ $complaint->type->label() }}</td>
                        <td><x-admin.status-badge :label="$complaint->status->label()" :tone="$complaint->status->tone()" /></td>
                        <td>{{ optional($complaint->created_at)->format('Y-m-d H:i') }}</td>
                        <td><a href="{{ route('admin.complaints.show', $complaint) }}" class="btn-secondary !px-4 !py-2">فتح</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-[var(--color-ink-500)]">لا توجد رسائل مطابقة للفلاتر الحالية.</td></tr>
                @endforelse
            </tbody>
        </table>

        <div class="px-5 py-4">{{ $complaints->links() }}</div>
    </x-admin.table-shell>
</x-layouts.admin>
