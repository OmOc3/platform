<x-layouts.admin title="المشرفون" heading="المشرفون" subheading="إدارة المدراء والموظفين وصلاحيات الوصول.">
    <x-admin.table-shell title="قائمة المشرفين" description="بحث، تصفية، وتصدير لإدارة مستخدمي لوحة التحكم.">
        <x-slot:actions>
            <a href="{{ route('admin.admins.create') }}" class="btn-primary">إضافة مشرف</a>
            <a href="{{ route('admin.admins.index', array_merge(request()->query(), ['export' => 'csv'])) }}" class="btn-secondary">تصدير CSV</a>
        </x-slot:actions>

        <x-slot:filters>
            <form method="GET" class="grid gap-3 md:grid-cols-[1.4fr_1fr_auto]">
            <input type="search" name="search" value="{{ request('search') }}" class="form-input" placeholder="ابحث بالاسم أو البريد أو المنصب" aria-label="ابحث في المشرفين">
                <select name="role" class="form-select">
                    <option value="">كل الأدوار</option>
                    @foreach ($roles as $role)
                        <option value="{{ $role->name }}" @selected(request('role') === $role->name)>{{ $role->name }}</option>
                    @endforeach
                </select>
                <button class="btn-secondary">تطبيق</button>
            </form>
        </x-slot:filters>

        <table class="data-table">
            <thead>
                <tr>
                    <th>الاسم</th>
                    <th>البريد</th>
                    <th>المنصب</th>
                    <th>الأدوار</th>
                    <th>الحالة</th>
                    <th>إجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($admins as $row)
                    <tr>
                        <td class="font-semibold">{{ $row->name }}</td>
                        <td>{{ $row->email }}</td>
                        <td>{{ $row->job_title ?: '—' }}</td>
                        <td>{{ $row->roles->pluck('name')->implode('، ') ?: 'بدون دور' }}</td>
                        <td>
                            <x-admin.status-badge :label="$row->is_active ? 'نشط' : 'متوقف'" :tone="$row->is_active ? 'success' : 'warning'" />
                        </td>
                        <td>
                            <div class="flex flex-wrap gap-2">
                                <a href="{{ route('admin.admins.edit', $row) }}" class="btn-secondary !px-4 !py-2">تعديل</a>
                                @if (!auth('admin')->id() || auth('admin')->id() !== $row->id)
                                    <form method="POST" action="{{ route('admin.admins.destroy', $row) }}" onsubmit="return confirm('تأكيد حذف المشرف؟');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn-danger">حذف</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-[var(--color-ink-500)]">لا يوجد مشرفون مطابقون.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="px-5 py-4">
            {{ $admins->links() }}
        </div>
    </x-admin.table-shell>
</x-layouts.admin>
