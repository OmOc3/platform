<x-layouts.admin title="متابعة طالب" heading="متابعة طالب" subheading="تحديث الحالة والبيانات الأساسية ومراجعة آخر مؤشرات المتابعة الخاصة بالطالب.">
    <div class="grid gap-6 xl:grid-cols-[1fr_0.95fr]">
        <section class="panel-tight">
            <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
                <div>
                    <p class="text-sm font-semibold text-[var(--color-brand-700)]">ملف الطالب</p>
                    <h2 class="mt-2 text-2xl font-bold">{{ $student->name }}</h2>
                    <p class="mt-2 text-sm text-[var(--color-ink-700)]">{{ $student->student_number }} / {{ $student->grade?->name_ar ?? 'بدون صف' }} / {{ $student->track?->name_ar ?? 'عام' }}</p>
                </div>
                <x-admin.status-badge
                    :label="$student->status->label()"
                    :tone="$student->status === \App\Shared\Enums\StudentStatus::Subscribed ? 'success' : ($student->status === \App\Shared\Enums\StudentStatus::Pending ? 'warning' : 'danger')"
                />
            </div>

            <form method="POST" action="{{ route('admin.students.update', $student) }}">
                @include('admin.students._form')
            </form>
        </section>

        <section class="space-y-6">
            <section class="panel-tight">
                <p class="text-sm font-semibold text-[var(--color-brand-700)]">ملخص المتابعة</p>
                <div class="mt-5 grid gap-3 md:grid-cols-2">
                    <div class="admin-workflow-card">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-ink-500)]">المتابع الإداري</p>
                        <p class="mt-3 font-semibold">{{ $student->ownerAdmin?->name ?? 'بدون تعيين' }}</p>
                    </div>
                    <div class="admin-workflow-card">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-ink-500)]">السنتر / المجموعة</p>
                        <p class="mt-3 font-semibold">{{ $student->center?->name_ar ?? '—' }}</p>
                        <p class="mt-1 text-sm text-[var(--color-ink-600)]">{{ $student->group?->name_ar ?? '—' }}</p>
                    </div>
                </div>
            </section>

            <section class="panel-tight">
                <div class="flex items-center justify-between gap-3">
                    <p class="text-sm font-semibold text-[var(--color-brand-700)]">آخر سجل حضور</p>
                    <span class="text-xs text-[var(--color-ink-500)]">{{ $attendancePreview->count() }} عناصر</span>
                </div>
                <div class="admin-mini-list mt-5">
                    @forelse ($attendancePreview as $record)
                        <article class="admin-mini-list__item">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="font-semibold">{{ $record->session?->title ?? 'جلسة بدون عنوان' }}</p>
                                    <p class="admin-mini-list__meta">{{ $record->session?->group?->center?->name_ar ?? '—' }} / {{ optional($record->recorded_at)->format('Y-m-d H:i') }}</p>
                                </div>
                                <x-admin.status-badge :label="$record->attendance_status->label()" :tone="$record->attendance_status->tone()" />
                            </div>
                        </article>
                    @empty
                        <div class="admin-workflow-card text-sm leading-7 text-[var(--color-ink-700)]">لا توجد سجلات حضور مرتبطة بهذا الطالب بعد.</div>
                    @endforelse
                </div>
            </section>

            <section class="panel-tight">
                <div class="flex items-center justify-between gap-3">
                    <p class="text-sm font-semibold text-[var(--color-brand-700)]">الشكاوى والاقتراحات</p>
                    <span class="text-xs text-[var(--color-ink-500)]">{{ $complaintsPreview->count() }} عناصر</span>
                </div>
                <div class="admin-mini-list mt-5">
                    @forelse ($complaintsPreview as $complaint)
                        <article class="admin-mini-list__item">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="font-semibold">{{ $complaint->type->value === 'suggestion' ? 'اقتراح' : 'شكوى' }}</p>
                                    <p class="mt-2 text-sm leading-7 text-[var(--color-ink-700)]">{{ str($complaint->content)->limit(110) }}</p>
                                </div>
                                <x-admin.status-badge :label="$complaint->status->label()" :tone="$complaint->status->tone()" />
                            </div>
                        </article>
                    @empty
                        <div class="admin-workflow-card text-sm leading-7 text-[var(--color-ink-700)]">لم يرسل هذا الطالب شكاوى أو اقتراحات بعد.</div>
                    @endforelse
                </div>
            </section>

            <section class="panel-tight">
                <p class="text-sm font-semibold text-[var(--color-brand-700)]">سجل الحالة</p>
                <div class="mt-5 space-y-4">
                    @forelse ($student->statusHistories as $history)
                        <article class="admin-mini-list__item">
                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <p class="font-semibold">{{ $history->new_status->label() }}</p>
                                <span class="text-xs text-[var(--color-ink-500)]">{{ optional($history->created_at)->format('Y-m-d H:i') }}</span>
                            </div>
                            <p class="mt-2 text-sm text-[var(--color-ink-700)]">
                                من {{ $history->previous_status?->label() ?? 'بداية السجل' }} إلى {{ $history->new_status->label() }}
                            </p>
                            @if ($history->reason)
                                <p class="mt-2 text-sm leading-8 text-[var(--color-ink-700)]">{{ $history->reason }}</p>
                            @endif
                            <p class="mt-2 text-xs text-[var(--color-ink-500)]">بواسطة: {{ $history->actor?->name ?? 'النظام / الطالب' }}</p>
                        </article>
                    @empty
                        <div class="admin-workflow-card text-sm leading-7 text-[var(--color-ink-700)]">لا يوجد سجل حالة محفوظ لهذا الطالب بعد.</div>
                    @endforelse
                </div>
            </section>
        </section>
    </div>
</x-layouts.admin>
