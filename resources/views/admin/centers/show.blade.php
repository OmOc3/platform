<x-layouts.admin :title="$center->name_ar" :heading="$center->name_ar" subheading="مراجعة المجموعات المرتبطة، آخر الجلسات، والطلاب المرتبطين بهذا السنتر.">
    <section class="grid gap-6 xl:grid-cols-[1.05fr_0.95fr]">
        <section class="space-y-6">
            <section class="panel-tight">
                <div class="grid gap-4 md:grid-cols-3">
                    <x-admin.metric-card label="المجموعات" :value="$center->groups->count()" description="إجمالي المجموعات المرتبطة بهذا السنتر." />
                    <x-admin.metric-card label="الطلاب" :value="$center->students_count" description="حسابات طلابية مرتبطة بالسنتر." />
                    <x-admin.metric-card label="جلسات مسجلة" :value="$center->groups->sum('sessions_count')" description="إجمالي الجلسات المرتبطة بالمجموعات." />
                </div>
            </section>

            <x-admin.table-shell title="المجموعات" description="عرض المجموعات الحالية مع أعداد الطلاب والجلسات داخل كل مجموعة.">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>المجموعة</th>
                            <th>الطلاب</th>
                            <th>الجلسات</th>
                            <th>الحالة</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($center->groups as $group)
                            <tr>
                                <td>
                                    <p class="font-semibold">{{ $group->name_ar }}</p>
                                    <p class="mt-1 text-xs text-[var(--color-ink-500)]">{{ $group->schedule_note ?: 'بدون ملاحظة مواعيد' }}</p>
                                </td>
                                <td>{{ $group->students_count }}</td>
                                <td>{{ $group->sessions_count }}</td>
                                <td><x-admin.status-badge :label="$group->is_active ? 'نشطة' : 'متوقفة'" :tone="$group->is_active ? 'success' : 'warning'" /></td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-[var(--color-ink-500)]">لا توجد مجموعات مرتبطة بهذا السنتر بعد.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </x-admin.table-shell>
        </section>

        <aside class="space-y-6">
            <section class="panel-tight">
                <div class="flex items-center justify-between gap-3">
                    <p class="text-sm font-semibold text-[var(--color-brand-700)]">آخر الجلسات</p>
                    <a href="{{ route('admin.attendance.index', ['center_id' => $center->id]) }}" class="text-sm font-semibold text-[var(--color-brand-700)]">كل التقرير</a>
                </div>
                <div class="admin-mini-list mt-5">
                    @forelse ($recentSessions as $session)
                        <article class="admin-mini-list__item">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="font-semibold">{{ $session->title }}</p>
                                    <p class="admin-mini-list__meta">{{ $session->group?->name_ar ?? '—' }} / {{ optional($session->starts_at)->format('Y-m-d H:i') }}</p>
                                </div>
                                <x-admin.status-badge :label="$session->session_type === 'exam' ? 'اختبار' : 'محاضرة'" :tone="$session->session_type === 'exam' ? 'warning' : 'neutral'" />
                            </div>
                            <p class="mt-3 text-xs text-[var(--color-ink-500)]">حضور: {{ $session->present_records_count }} / تأخير: {{ $session->late_records_count }} / غياب: {{ $session->absent_records_count }}</p>
                        </article>
                    @empty
                        <div class="admin-workflow-card text-sm leading-7 text-[var(--color-ink-700)]">لا توجد جلسات مسجلة لهذا السنتر بعد.</div>
                    @endforelse
                </div>
            </section>

            <section class="panel-tight">
                <p class="text-sm font-semibold text-[var(--color-brand-700)]">آخر الطلاب المرتبطين</p>
                <div class="admin-mini-list mt-5">
                    @forelse ($recentStudents as $student)
                        <article class="admin-mini-list__item">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="font-semibold">{{ $student->name }}</p>
                                    <p class="admin-mini-list__meta">{{ $student->group?->name_ar ?? 'بدون مجموعة' }} / {{ $student->ownerAdmin?->name ?? 'بدون متابع' }}</p>
                                </div>
                                <x-admin.status-badge
                                    :label="$student->status->label()"
                                    :tone="$student->status === \App\Shared\Enums\StudentStatus::Subscribed ? 'success' : ($student->status === \App\Shared\Enums\StudentStatus::Pending ? 'warning' : 'danger')"
                                />
                            </div>
                        </article>
                    @empty
                        <div class="admin-workflow-card text-sm leading-7 text-[var(--color-ink-700)]">لا توجد حسابات طلابية مرتبطة بهذا السنتر بعد.</div>
                    @endforelse
                </div>
            </section>
        </aside>
    </section>
</x-layouts.admin>
