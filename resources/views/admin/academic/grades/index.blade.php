<x-layouts.admin title="الصفوف" heading="الصفوف" subheading="إدارة الصفوف الدراسية كأساس للمحتوى والمسارات.">
    <x-admin.table-shell title="الصفوف الدراسية" description="بحث وتصفية وتصدير لهيكل الأكاديمية الدراسي.">
        <x-slot:actions>
            <a href="{{ route('admin.grades.create') }}" class="btn-primary">إضافة صف</a>
            <a href="{{ route('admin.grades.index', array_merge(request()->query(), ['export' => 'csv'])) }}" class="btn-secondary">تصدير CSV</a>
        </x-slot:actions>

        <x-slot:filters>
            <form method="GET" class="grid gap-3 md:grid-cols-[1fr_220px_auto]">
            <input type="search" name="search" value="{{ request('search') }}" class="form-input" placeholder="ابحث بالاسم أو الكود" aria-label="ابحث في الصفوف">
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
                    <th>الصف</th>
                    <th>الكود</th>
                    <th>الترتيب</th>
                    <th>الحالة</th>
                    <th>إجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($grades as $row)
                    <tr>
                        <td class="font-semibold">{{ $row->name_ar }}</td>
                        <td class="font-mono text-xs">{{ $row->code }}</td>
                        <td>{{ $row->sort_order }}</td>
                        <td>
                            <x-admin.status-badge :label="$row->is_active ? 'نشط' : 'متوقف'" :tone="$row->is_active ? 'success' : 'warning'" />
                        </td>
                        <td>
                            <div class="flex flex-wrap gap-2">
                                <a href="{{ route('admin.grades.edit', $row) }}" class="btn-secondary !px-4 !py-2">تعديل</a>
                                <form method="POST" action="{{ route('admin.grades.destroy', $row) }}" onsubmit="return confirm('تأكيد حذف الصف؟');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn-danger">حذف</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-[var(--color-ink-500)]">لا توجد صفوف بعد.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="px-5 py-4">
            {{ $grades->links() }}
        </div>
    </x-admin.table-shell>
</x-layouts.admin>
