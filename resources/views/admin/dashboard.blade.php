<x-layouts.admin title="لوحة التحكم" heading="لوحة التحكم" subheading="ملخص تشغيلي سريع للطلاب، الطلبات، الشكاوى، والسناتر من شاشة واحدة.">
    <section class="admin-metric-grid">
        @foreach ($stats as $stat)
            <x-admin.metric-card
                :label="$stat['label']"
                :value="$stat['value']"
                description="مؤشر مباشر يحتاجه فريق الإدارة في المتابعة اليومية."
            />
        @endforeach
    </section>

    <section class="grid gap-6 xl:grid-cols-[1.1fr_0.9fr]">
        <x-admin.table-shell title="سجل المراجعة الأخير" description="أي تغيير تشغيلي أو أكاديمي حساس يمر عبر هذا السجل لسهولة التتبع.">
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
                            <td class="font-semibold">{{ $log->event }}</td>
                            <td>{{ class_basename($log->actor_type ?? '') }} #{{ $log->actor_id ?? '-' }}</td>
                            <td>{{ optional($log->created_at)->format('Y-m-d H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center text-[var(--color-ink-500)]">لا توجد عمليات مسجلة بعد.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </x-admin.table-shell>

        <section class="panel-tight space-y-6">
            <div>
                <p class="text-sm font-semibold text-[var(--color-brand-700)]">مؤشرات مساندة</p>
                <div class="mt-4 grid gap-3 sm:grid-cols-2">
                    @foreach ($secondaryStats as $stat)
                        <div class="admin-workflow-card">
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-ink-500)]">{{ $stat['label'] }}</p>
                            <p class="mt-3 text-2xl font-bold text-[var(--color-brand-700)]">{{ $stat['value'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                <section>
                    <div class="flex items-center justify-between gap-3">
                        <h2 class="text-lg font-bold">طلبات التسجيل</h2>
                        <a href="{{ route('admin.students.index', ['status' => \App\Shared\Enums\StudentStatus::Pending->value]) }}" class="text-sm font-semibold text-[var(--color-brand-700)]">كل الطلبات</a>
                    </div>
                    <div class="admin-mini-list mt-4">
                        @forelse ($attentionItems['registrations'] as $student)
                            <article class="admin-mini-list__item">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="font-semibold">{{ $student->name }}</p>
                                        <p class="admin-mini-list__meta">{{ $student->grade?->name_ar ?? 'بدون صف' }} / {{ $student->track?->name_ar ?? 'عام' }}</p>
                                    </div>
                                    <x-admin.status-badge :label="$student->status->label()" tone="warning" />
                                </div>
                            </article>
                        @empty
                            <div class="admin-workflow-card text-sm leading-7 text-[var(--color-ink-700)]">لا توجد طلبات تسجيل تنتظر المراجعة الآن.</div>
                        @endforelse
                    </div>
                </section>

                <section>
                    <div class="flex items-center justify-between gap-3">
                        <h2 class="text-lg font-bold">شكاوى تحتاج رد</h2>
                        <a href="{{ route('admin.complaints.index') }}" class="text-sm font-semibold text-[var(--color-brand-700)]">عرض الكل</a>
                    </div>
                    <div class="admin-mini-list mt-4">
                        @forelse ($attentionItems['complaints'] as $complaint)
                            <article class="admin-mini-list__item">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="font-semibold">{{ $complaint->student?->name ?? 'طالب غير معروف' }}</p>
                                        <p class="mt-2 text-sm leading-7 text-[var(--color-ink-700)]">{{ str($complaint->content)->limit(90) }}</p>
                                    </div>
                                    <x-admin.status-badge :label="$complaint->status->label()" :tone="$complaint->status->tone()" />
                                </div>
                            </article>
                        @empty
                            <div class="admin-workflow-card text-sm leading-7 text-[var(--color-ink-700)]">لا توجد شكاوى مفتوحة حاليًا.</div>
                        @endforelse
                    </div>
                </section>
            </div>
        </section>
    </section>
</x-layouts.admin>
