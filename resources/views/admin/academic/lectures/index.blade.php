<x-layouts.admin title="المحتوى الأكاديمي" heading="المحتوى الأكاديمي" subheading="إدارة المحاضرات والمراجعات والمواد القابلة للشراء أو الإتاحة المجانية داخل البوابة.">
    <x-admin.table-shell title="المحتوى الأكاديمي" description="بحث، تصفية، وتصدير للمحاضرات والمراجعات حسب الصف والنوع والحالة.">
        <x-slot:actions>
            <a href="{{ route('admin.lectures.create') }}" class="btn-primary">إضافة عنصر</a>
            <a href="{{ route('admin.lectures.index', array_merge(request()->query(), ['export' => 'csv'])) }}" class="btn-secondary">تصدير CSV</a>
        </x-slot:actions>

        <x-slot:filters>
            <form method="GET" class="grid gap-3 md:grid-cols-[1fr_220px_220px_220px_auto]">
                <input type="search" name="search" value="{{ request('search') }}" class="form-input" placeholder="ابحث بالعنوان أو الرابط">
                <select name="grade_id" class="form-select">
                    <option value="">كل الصفوف</option>
                    @foreach ($grades as $grade)
                        <option value="{{ $grade->id }}" @selected((string) request('grade_id') === (string) $grade->id)>{{ $grade->name_ar }}</option>
                    @endforeach
                </select>
                <select name="type" class="form-select">
                    <option value="">كل الأنواع</option>
                    @foreach ($types as $type)
                        <option value="{{ $type->value }}" @selected(request('type') === $type->value)>{{ $type->value }}</option>
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
                    <th>النوع</th>
                    <th>الصف</th>
                    <th>السعر</th>
                    <th>الحالة</th>
                    <th>إجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($lectures as $row)
                    <tr>
                        <td class="font-semibold">{{ $row->title }}</td>
                        <td>{{ $row->type->value }}</td>
                        <td>{{ $row->grade?->name_ar }}</td>
                        <td>{{ number_format($row->price_amount) }} {{ $row->currency }}</td>
                        <td><x-admin.status-badge :label="$row->is_active ? 'نشط' : 'متوقف'" :tone="$row->is_active ? 'success' : 'warning'" /></td>
                        <td>
                            <div class="flex flex-wrap gap-2">
                                <a href="{{ route('admin.lectures.edit', $row) }}" class="btn-secondary !px-4 !py-2">تعديل</a>
                                <a href="{{ route('admin.lectures.progress.index', $row) }}" class="btn-secondary !px-4 !py-2">تقدم الطلاب</a>
                                <form method="POST" action="{{ route('admin.lectures.destroy', $row) }}" onsubmit="return confirm('تأكيد حذف المحتوى؟');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn-danger">حذف</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-[var(--color-ink-500)]">لا يوجد محتوى بعد.</td></tr>
                @endforelse
            </tbody>
        </table>

        <div class="px-5 py-4">{{ $lectures->links() }}</div>
    </x-admin.table-shell>
</x-layouts.admin>
