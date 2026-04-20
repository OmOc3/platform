<x-layouts.admin title="السناتر" heading="السناتر" subheading="متابعة السناتر التعليمية والمجموعات التابعة لها مع أعداد الطلاب والجلسات.">
    <section class="admin-metric-grid">
        <x-admin.metric-card label="كل السناتر" :value="$overview['total']" description="إجمالي السناتر المسجلة." />
        <x-admin.metric-card label="سناتر فعالة" :value="$overview['active']" description="السناتر المتاحة حاليًا للربط مع الطلاب." />
        <x-admin.metric-card label="المجموعات" :value="$overview['groups']" description="كل المجموعات المرتبطة بالسناتر." />
        <x-admin.metric-card label="طلاب السناتر" :value="$overview['students']" description="طلاب لديهم انتساب سنتر محفوظ على الحساب." />
    </section>

    <x-admin.table-shell title="السناتر التعليمية" description="عرض مراكز التعليم والمجموعات المرتبطة وعدد الطلاب المتابعين داخل كل سنتر.">
        <x-slot:actions>
            <a href="{{ route('admin.centers.index', array_merge(request()->query(), ['export' => 'csv'])) }}" class="btn-secondary">تصدير CSV</a>
        </x-slot:actions>

        <x-slot:filters>
            <form method="GET" class="grid gap-3 md:grid-cols-[1fr_220px_auto]">
                <div>
                    <label class="field-label" for="centers_search">ابحث في السناتر</label>
                    <input id="centers_search" type="search" name="search" value="{{ request('search') }}" class="form-input" placeholder="اسم السنتر، المجموعة، أو المدينة">
                </div>
                <div>
                    <label class="field-label" for="centers_status">حالة السنتر</label>
                    <select id="centers_status" name="status" class="form-select">
                        <option value="">كل الحالات</option>
                        <option value="active" @selected(request('status') === 'active')>نشط</option>
                        <option value="inactive" @selected(request('status') === 'inactive')>متوقف</option>
                    </select>
                </div>
                <button class="btn-secondary md:self-end">تطبيق الفلاتر</button>
            </form>
        </x-slot:filters>

        <table class="data-table">
            <thead>
                <tr>
                    <th>السنتر</th>
                    <th>المدينة</th>
                    <th>المجموعات</th>
                    <th>الطلاب</th>
                    <th>الحالة</th>
                    <th>إجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($centers as $center)
                    <tr>
                        <td>
                            <p class="font-semibold">{{ $center->name_ar }}</p>
                            <p class="mt-1 text-xs text-[var(--color-ink-500)]">{{ $center->groups->pluck('name_ar')->take(2)->join('، ') ?: 'بدون مجموعات' }}</p>
                        </td>
                        <td>{{ $center->city ?: '—' }}</td>
                        <td>
                            <p class="font-semibold">{{ $center->groups_count }}</p>
                            <p class="mt-1 text-xs text-[var(--color-ink-500)]">{{ $center->active_groups_count }} مجموعة نشطة</p>
                        </td>
                        <td>{{ $center->students_count }}</td>
                        <td><x-admin.status-badge :label="$center->is_active ? 'نشط' : 'متوقف'" :tone="$center->is_active ? 'success' : 'warning'" /></td>
                        <td><a href="{{ route('admin.centers.show', $center) }}" class="btn-secondary !px-4 !py-2">التفاصيل</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-[var(--color-ink-500)]">لا توجد سناتر مطابقة للفلاتر الحالية.</td></tr>
                @endforelse
            </tbody>
        </table>

        <div class="px-5 py-4">{{ $centers->links() }}</div>
    </x-admin.table-shell>
</x-layouts.admin>
