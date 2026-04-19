<x-layouts.admin title="لوحة التحكم" heading="لوحة التحكم" subheading="نظرة تشغيلية سريعة على وحدات Milestone 1.">
    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        @foreach ($stats as $stat)
            <article class="kpi-card">
                <p class="text-sm font-semibold text-[var(--color-ink-700)]">{{ $stat['label'] }}</p>
                <p class="mt-6 text-4xl font-bold">{{ $stat['value'] }}</p>
            </article>
        @endforeach
    </section>

    <section class="grid gap-6 xl:grid-cols-[1.2fr_0.8fr]">
        <x-admin.table-shell title="آخر سجل مراجعة" description="أي تغيير حساس في الإدارة يمر عبر سجل المراجعة.">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>الحدث</th>
                        <th>الفاعل</th>
                        <th>التاريخ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($latestAuditLogs as $log)
                        <tr>
                            <td>{{ $log->event }}</td>
                            <td>{{ class_basename($log->actor_type ?? '') }} #{{ $log->actor_id ?? '-' }}</td>
                            <td>{{ optional($log->created_at)->format('Y-m-d H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center text-[var(--color-ink-500)]">لا توجد عمليات بعد.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </x-admin.table-shell>

        <section class="panel-tight space-y-4">
            <div>
                <p class="text-sm font-semibold text-[var(--color-brand-700)]">نطاق المرحلة الحالية</p>
                <h2 class="mt-2 text-2xl font-bold">Milestones 0-1</h2>
            </div>
            <ul class="space-y-3 text-sm leading-7 text-[var(--color-ink-700)]">
                <li>إدارة الحراس والصلاحيات والمشرفين.</li>
                <li>إعدادات تشغيلية قابلة للتوسع.</li>
                <li>صفوف ومسارات أكاديمية كبنية أساسية للمحتوى.</li>
                <li>سجل مراجعة للعمليات الحساسة.</li>
            </ul>
        </section>
    </section>
</x-layouts.admin>
