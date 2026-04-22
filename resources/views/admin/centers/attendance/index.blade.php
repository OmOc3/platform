<x-layouts.admin title="تقارير الحضور" heading="تقارير الحضور" subheading="قراءة جلسات السنتر، توزيع الحضور، ثم فتح كل جلسة لتسجيل الروستر وتحديث الدرجات المرتبطة بها.">
    <section class="admin-metric-grid">
        <x-admin.metric-card label="كل الجلسات" :value="$overview['total']" description="إجمالي الجلسات المرتبطة بالمجموعات." />
        <x-admin.metric-card label="جلسات محاضرات" :value="$overview['lectures']" description="الجلسات التعليمية العادية." />
        <x-admin.metric-card label="جلسات اختبارات" :value="$overview['exams']" description="جلسات امتحانات أو تقييمات داخل السنتر." />
        <x-admin.metric-card label="سجلات الحضور" :value="$overview['records']" description="كل حالات الحضور المسجلة على الجلسات." />
    </section>

    <x-admin.table-shell title="تقرير الحضور" description="فلترة حسب السنتر أو المجموعة أو نوع الجلسة ثم فتح كل جلسة لإدارة الروستر وتحديث الحضور.">
        <x-slot:actions>
            <a href="{{ route('admin.attendance.index', array_merge(request()->query(), ['export' => 'csv'])) }}" class="btn-secondary">تصدير CSV</a>
        </x-slot:actions>

        <x-slot:filters>
            <form method="GET" class="grid gap-3 md:grid-cols-[1fr_220px_220px_220px_auto]">
                <input type="search" name="search" value="{{ request('search') }}" class="form-input" placeholder="ابحث بعنوان الجلسة أو اسم المجموعة" aria-label="ابحث في جلسات الحضور">
                <select name="center_id" class="form-select">
                    <option value="">كل السناتر</option>
                    @foreach ($centers as $center)
                        <option value="{{ $center->id }}" @selected((string) request('center_id') === (string) $center->id)>{{ $center->name_ar }}</option>
                    @endforeach
                </select>
                <select name="group_id" class="form-select">
                    <option value="">كل المجموعات</option>
                    @foreach ($groups as $group)
                        <option value="{{ $group->id }}" @selected((string) request('group_id') === (string) $group->id)>{{ $group->name_ar }}</option>
                    @endforeach
                </select>
                <select name="session_type" class="form-select">
                    <option value="">كل الأنواع</option>
                    <option value="lecture" @selected(request('session_type') === 'lecture')>محاضرة</option>
                    <option value="exam" @selected(request('session_type') === 'exam')>اختبار</option>
                </select>
                <button class="btn-secondary">تطبيق</button>
            </form>
        </x-slot:filters>

        <table class="data-table">
            <thead>
                <tr>
                    <th>الجلسة</th>
                    <th>السنتر / المجموعة</th>
                    <th>النوع</th>
                    <th>الحضور</th>
                    <th>التاريخ</th>
                    <th>إجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($sessions as $session)
                    <tr>
                        <td class="font-semibold">{{ $session->title }}</td>
                        <td>
                            <p>{{ $session->group?->center?->name_ar ?? '—' }}</p>
                            <p class="mt-1 text-xs text-[var(--color-ink-500)]">{{ $session->group?->name_ar ?? '—' }}</p>
                        </td>
                        <td>
                            <x-admin.status-badge :label="$session->session_type === 'exam' ? 'اختبار' : 'محاضرة'" :tone="$session->session_type === 'exam' ? 'warning' : 'neutral'" />
                        </td>
                        <td>
                            <p class="font-semibold">حضور {{ $session->present_records_count }}</p>
                            <p class="mt-1 text-xs text-[var(--color-ink-500)]">تأخير {{ $session->late_records_count }} / غياب {{ $session->absent_records_count }}</p>
                        </td>
                        <td>{{ optional($session->starts_at)->format('Y-m-d H:i') ?: '—' }}</td>
                        <td>
                            <a href="{{ route('admin.attendance.show', $session) }}" class="btn-secondary !px-4 !py-2">تفاصيل الجلسة</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-[var(--color-ink-500)]">لا توجد جلسات مطابقة للفلاتر الحالية.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="px-5 py-4">{{ $sessions->links() }}</div>
    </x-admin.table-shell>
</x-layouts.admin>
