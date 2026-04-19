<x-layouts.admin title="الطلاب" heading="الطلاب" subheading="CRM starter لمتابعة التسجيلات الذاتية وتقسيمات الصف والمصدر والحالة.">
    <x-admin.table-shell title="قائمة الطلاب" description="بحث وتصفية وتصدير سريع لسجل الطلاب الحالي.">
        <x-slot:actions>
            <a href="{{ route('admin.students.index', array_merge(request()->query(), ['export' => 'csv'])) }}" class="btn-secondary">تصدير CSV</a>
        </x-slot:actions>

        <x-slot:filters>
            <form method="GET" class="grid gap-3 lg:grid-cols-6">
                <input type="search" name="search" value="{{ request('search') }}" class="form-input lg:col-span-2" placeholder="الاسم أو البريد أو الهاتف أو الرقم">
                <select name="status" class="form-select">
                    <option value="">كل الحالات</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->value }}</option>
                    @endforeach
                </select>
                <select name="source_type" class="form-select">
                    <option value="">كل المصادر</option>
                    @foreach ($sourceTypes as $sourceType)
                        <option value="{{ $sourceType->value }}" @selected(request('source_type') === $sourceType->value)>{{ $sourceType->value }}</option>
                    @endforeach
                </select>
                <select name="grade_id" class="form-select">
                    <option value="">كل الصفوف</option>
                    @foreach ($grades as $grade)
                        <option value="{{ $grade->id }}" @selected((string) request('grade_id') === (string) $grade->id)>{{ $grade->name_ar }}</option>
                    @endforeach
                </select>
                <select name="owner_admin_id" class="form-select">
                    <option value="">كل الملاك</option>
                    @foreach ($owners as $owner)
                        <option value="{{ $owner->id }}" @selected((string) request('owner_admin_id') === (string) $owner->id)>{{ $owner->name }}</option>
                    @endforeach
                </select>
                <label class="flex items-center gap-2 text-sm text-[var(--color-ink-700)]">
                    <input type="checkbox" name="is_azhar" value="1" class="h-4 w-4 rounded border-[var(--color-brand-200)] text-[var(--color-brand-700)]" @checked(request()->boolean('is_azhar'))>
                    أزهري فقط
                </label>
                <button class="btn-secondary lg:col-span-1">تطبيق</button>
            </form>
        </x-slot:filters>

        <table class="data-table">
            <thead>
                <tr>
                    <th>الطالب</th>
                    <th>الرقم</th>
                    <th>الصف / المسار</th>
                    <th>المصدر</th>
                    <th>الحالة</th>
                    <th>المالك</th>
                    <th>إجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($students as $row)
                    <tr>
                        <td>
                            <p class="font-semibold">{{ $row->name }}</p>
                            <p class="mt-1 text-xs text-[var(--color-ink-500)]">{{ $row->email }}</p>
                        </td>
                        <td>{{ $row->student_number }}</td>
                        <td>{{ $row->grade?->name_ar ?: '—' }} / {{ $row->track?->name_ar ?: '—' }}</td>
                        <td>{{ $row->source_type?->value ?: '—' }}</td>
                        <td>
                            <x-admin.status-badge :label="$row->status->value" :tone="in_array($row->status->value, ['subscribed'], true) ? 'success' : (in_array($row->status->value, ['blocked', 'refused'], true) ? 'danger' : 'warning')" />
                        </td>
                        <td>{{ $row->ownerAdmin?->name ?: '—' }}</td>
                        <td><a href="{{ route('admin.students.edit', $row) }}" class="btn-secondary !px-4 !py-2">متابعة</a></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-[var(--color-ink-500)]">لا يوجد طلاب مطابقون.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="px-5 py-4">
            {{ $students->links() }}
        </div>
    </x-admin.table-shell>
</x-layouts.admin>
