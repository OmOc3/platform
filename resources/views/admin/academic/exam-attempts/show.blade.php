<x-layouts.admin title="تفاصيل المحاولة" heading="تفاصيل المحاولة" subheading="عرض تفصيلي لمحاولة الطالب ونتيجة كل سؤال.">
    <section class="grid gap-6 xl:grid-cols-[0.9fr_1.1fr]">
        <article class="panel-tight space-y-5">
            <div class="flex flex-wrap items-center gap-3">
                <x-admin.status-badge :label="$examAttempt->status->label()" :tone="$examAttempt->status->tone()" />
                @if (data_get($examAttempt->result_meta, 'submitted_by_timer'))
                    <x-admin.status-badge label="إرسال تلقائي بانتهاء الوقت" tone="warning" />
                @endif
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div class="rounded-[1.4rem] bg-[var(--color-brand-50)] p-4">
                    <p class="text-xs text-[var(--color-ink-500)]">الطالب</p>
                    <p class="mt-2 font-semibold">{{ $examAttempt->student?->name }}</p>
                    <p class="mt-1 text-xs text-[var(--color-ink-500)]">{{ $examAttempt->student?->student_number }}</p>
                </div>
                <div class="rounded-[1.4rem] bg-[var(--color-brand-50)] p-4">
                    <p class="text-xs text-[var(--color-ink-500)]">الاختبار</p>
                    <p class="mt-2 font-semibold">{{ $examAttempt->exam?->title }}</p>
                    <p class="mt-1 text-xs text-[var(--color-ink-500)]">{{ $examAttempt->exam?->grade?->name_ar }}</p>
                </div>
                <div class="rounded-[1.4rem] bg-[var(--color-brand-50)] p-4">
                    <p class="text-xs text-[var(--color-ink-500)]">النتيجة</p>
                    <p class="mt-2 font-semibold">{{ $examAttempt->total_score }}/{{ $examAttempt->max_score }}</p>
                </div>
                <div class="rounded-[1.4rem] bg-[var(--color-brand-50)] p-4">
                    <p class="text-xs text-[var(--color-ink-500)]">إجابات صحيحة / خاطئة / متخطاة</p>
                    <p class="mt-2 font-semibold">{{ $examAttempt->correct_answers_count }} / {{ $wrongCount }} / {{ $skippedCount }}</p>
                </div>
            </div>

            <div class="surface-outline surface-outline--brand rounded-[1.4rem] p-4">
                <p class="text-xs text-[var(--color-ink-500)]">الطوابع الزمنية</p>
                <dl class="mt-3 grid gap-3 sm:grid-cols-3">
                    <div>
                        <dt class="text-xs text-[var(--color-ink-500)]">بدأت في</dt>
                        <dd class="mt-1 font-semibold">{{ optional($examAttempt->started_at)->format('Y/m/d H:i') }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-[var(--color-ink-500)]">أُرسلت في</dt>
                        <dd class="mt-1 font-semibold">{{ optional($examAttempt->submitted_at)->format('Y/m/d H:i') ?: '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-[var(--color-ink-500)]">صُححت في</dt>
                        <dd class="mt-1 font-semibold">{{ optional($examAttempt->graded_at)->format('Y/m/d H:i') ?: '—' }}</dd>
                    </div>
                </dl>
            </div>
        </article>

        <article class="space-y-4">
            @foreach ($questionResults as $index => $result)
                <section class="panel-tight">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <p class="text-sm font-semibold text-[var(--color-brand-700)]">السؤال {{ $index + 1 }}</p>
                        <x-admin.status-badge :label="$result['is_correct'] ? 'صحيح' : 'خاطئ أو غير مجاب'" :tone="$result['is_correct'] ? 'success' : 'danger'" />
                    </div>

                    <h2 class="mt-4 text-lg font-semibold leading-9">{{ $result['question']?->prompt }}</h2>

                    <div class="mt-5 grid gap-4 md:grid-cols-2">
                        <div class="rounded-[1.4rem] bg-[var(--color-brand-50)] p-4">
                            <p class="text-xs text-[var(--color-ink-500)]">إجابة الطالب</p>
                            <p class="mt-2 font-semibold">{{ $result['selected_choice'] }}</p>
                        </div>
                        <div class="rounded-[1.4rem] bg-[var(--color-brand-50)] p-4">
                            <p class="text-xs text-[var(--color-ink-500)]">الإجابة الصحيحة</p>
                            <p class="mt-2 font-semibold">{{ $result['correct_choice'] ?? '—' }}</p>
                        </div>
                    </div>

                    <div class="surface-outline surface-outline--brand mt-4 rounded-[1.4rem] p-4">
                        <p class="text-xs text-[var(--color-ink-500)]">التفسير</p>
                        <p class="mt-2 text-sm leading-8 text-[var(--color-ink-700)]">{{ $result['explanation'] ?: 'لا يوجد تفسير مسجل لهذا السؤال.' }}</p>
                    </div>
                </section>
            @endforeach
        </article>
    </section>
</x-layouts.admin>
