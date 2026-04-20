<x-layouts.admin title="الاختبارات" heading="الاختبارات" subheading="إدارة الكتالوج الأولي للاختبارات وربطها بالمحاضرات أو نشرها كعناصر مستقلة.">
    <x-admin.table-shell title="الاختبارات" description="عرض الاختبارات الحالية مع الصف والحالة وعدد الأسئلة.">
        <x-slot:actions>
            <a href="{{ route('admin.exams.create') }}" class="btn-primary">إضافة اختبار</a>
            <a href="{{ route('admin.exams.index', array_merge(request()->query(), ['export' => 'csv'])) }}" class="btn-secondary">تصدير CSV</a>
        </x-slot:actions>

        <x-slot:filters>
            <form method="GET" class="grid gap-3 md:grid-cols-[1fr_220px_220px_auto]">
            <input type="search" name="search" value="{{ request('search') }}" class="form-input" placeholder="ابحث بالعنوان أو الوصف" aria-label="ابحث في الاختبارات">
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
                    <th>العنوان</th>
                    <th>المحاضرة</th>
                    <th>الصف</th>
                    <th>المدة</th>
                    <th>الحالة</th>
                    <th>إجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($exams as $row)
                    <tr>
                        <td class="font-semibold">{{ $row->title }}</td>
                        <td>{{ $row->lecture?->title ?? 'اختبار مستقل' }}</td>
                        <td>{{ $row->grade?->name_ar }}</td>
                        <td>{{ $row->duration_minutes ? $row->duration_minutes.' دقيقة' : '—' }}</td>
                        <td><x-admin.status-badge :label="$row->is_active ? 'نشط' : 'متوقف'" :tone="$row->is_active ? 'success' : 'warning'" /></td>
                        <td>
                            <div class="flex flex-wrap gap-2">
                                <a href="{{ route('admin.exams.edit', $row) }}" class="btn-secondary !px-4 !py-2">تعديل</a>
                                <form method="POST" action="{{ route('admin.exams.destroy', $row) }}" onsubmit="return confirm('تأكيد حذف الاختبار؟');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn-danger">حذف</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-[var(--color-ink-500)]">لا توجد اختبارات بعد.</td></tr>
                @endforelse
            </tbody>
        </table>

        <div class="px-5 py-4">{{ $exams->links() }}</div>
    </x-admin.table-shell>
</x-layouts.admin>
