<x-layouts.admin title="أقسام المنهج" heading="أقسام المنهج" subheading="هيكلة المحتوى على مستوى المنهج والصف لتسهيل التصنيف داخل البوابة والكتالوج.">
    <x-admin.table-shell title="أقسام المنهج" description="بحث، تصفية، وتصدير لأقسام المنهج النشطة وغير النشطة.">
        <x-slot:actions>
            <a href="{{ route('admin.curriculum-sections.create') }}" class="btn-primary">إضافة قسم</a>
            <a href="{{ route('admin.curriculum-sections.index', array_merge(request()->query(), ['export' => 'csv'])) }}" class="btn-secondary">تصدير CSV</a>
        </x-slot:actions>

        <x-slot:filters>
            <form method="GET" class="grid gap-3 md:grid-cols-[1fr_220px_220px_auto]">
                <input type="search" name="search" value="{{ request('search') }}" class="form-input" placeholder="ابحث بالاسم أو الرابط">
                <select name="grade_id" class="form-select">
                    <option value="">كل الصفوف</option>
                    @foreach ($grades as $grade)
                        <option value="{{ $grade->id }}" @selected((string) request('grade_id') === (string) $grade->id)>{{ $grade->name_ar }}</option>
                    @endforeach
                </select>
                <select name="status" class="form-select">
                    <option value="">كل الحالات</option>
                    <option value="active" @selected(request('status') === 'active')>نشط</option>
                    <option value="inactive" @selected(request('status') === 'inactive')>متوقف</option>
                </select>
                <button class="btn-secondary">تطبيق</button>
            </form>
        </x-slot:filters>

        <table class="data-table">
            <thead>
                <tr>
                    <th>القسم</th>
                    <th>الصف</th>
                    <th>المسار</th>
                    <th>الحالة</th>
                    <th>إجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($sections as $row)
                    <tr>
                        <td class="font-semibold">{{ $row->name_ar }}</td>
                        <td>{{ $row->grade?->name_ar }}</td>
                        <td>{{ $row->track?->name_ar ?? 'عام' }}</td>
                        <td><x-admin.status-badge :label="$row->is_active ? 'نشط' : 'متوقف'" :tone="$row->is_active ? 'success' : 'warning'" /></td>
                        <td>
                            <div class="flex flex-wrap gap-2">
                                <a href="{{ route('admin.curriculum-sections.edit', $row) }}" class="btn-secondary !px-4 !py-2">تعديل</a>
                                <form method="POST" action="{{ route('admin.curriculum-sections.destroy', $row) }}" onsubmit="return confirm('تأكيد حذف القسم؟');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn-danger">حذف</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-[var(--color-ink-500)]">لا توجد أقسام بعد.</td></tr>
                @endforelse
            </tbody>
        </table>

        <div class="px-5 py-4">{{ $sections->links() }}</div>
    </x-admin.table-shell>
</x-layouts.admin>
