<x-layouts.admin title="الإعدادات" heading="الإعدادات" subheading="إدارة المفاتيح التشغيلية والهوية العامة للمنصة.">
    <x-admin.table-shell title="سجل الإعدادات" description="كل إعداد يُحفظ كعنصر مستقل مع نوع وقيمة قابلة للتوسع.">
        <x-slot:actions>
            <a href="{{ route('admin.settings.create') }}" class="btn-primary">إضافة إعداد</a>
            <a href="{{ route('admin.settings.index', array_merge(request()->query(), ['export' => 'csv'])) }}" class="btn-secondary">تصدير CSV</a>
        </x-slot:actions>

        <x-slot:filters>
            <form method="GET" class="grid gap-3 md:grid-cols-[1fr_1fr_auto]">
                <input type="search" name="search" value="{{ request('search') }}" class="form-input" placeholder="ابحث بالعنوان أو المفتاح أو القيمة">
                <input type="search" name="group" value="{{ request('group') }}" class="form-input" placeholder="المجموعة">
                <button class="btn-secondary">تطبيق</button>
            </form>
        </x-slot:filters>

        <table class="data-table">
            <thead>
                <tr>
                    <th>المجموعة</th>
                    <th>المفتاح</th>
                    <th>العنوان</th>
                    <th>النوع</th>
                    <th>القيمة</th>
                    <th>إجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($settings as $row)
                    <tr>
                        <td>{{ $row->group }}</td>
                        <td class="font-mono text-xs">{{ $row->key }}</td>
                        <td class="font-semibold">{{ $row->label }}</td>
                        <td>{{ $row->type->value }}</td>
                        <td class="max-w-xs truncate">{{ $row->value ?: '—' }}</td>
                        <td>
                            <div class="flex flex-wrap gap-2">
                                <a href="{{ route('admin.settings.edit', $row) }}" class="btn-secondary !px-4 !py-2">تعديل</a>
                                <form method="POST" action="{{ route('admin.settings.destroy', $row) }}" onsubmit="return confirm('تأكيد حذف الإعداد؟');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn-danger">حذف</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-[var(--color-ink-500)]">لا توجد إعدادات بعد.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="px-5 py-4">
            {{ $settings->links() }}
        </div>
    </x-admin.table-shell>
</x-layouts.admin>
