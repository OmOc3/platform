<x-layouts.student :title="'نتيجة: '.$examAttempt->exam->title" :heading="'نتيجة '.$examAttempt->exam->title" subheading="ملخص النتيجة الحالية مع مراجعة كل سؤال والإجابة الصحيحة.">
    <section class="panel-tight">
        <div class="flex flex-wrap items-center gap-3">
            <x-admin.status-badge :label="$examAttempt->status->label()" :tone="$examAttempt->status->tone()" />
            @if (data_get($examAttempt->result_meta, 'submitted_by_timer'))
                <x-admin.status-badge label="تم الإرسال بانتهاء الوقت" tone="warning" />
            @endif
        </div>

        <div class="mt-6 grid gap-4 md:grid-cols-4">
            <div class="rounded-[1.6rem] bg-[var(--color-brand-50)] p-4">
                <p class="text-xs text-[var(--color-ink-500)]">الدرجة النهائية</p>
                <p class="mt-2 text-2xl font-bold">{{ $examAttempt->total_score }}/{{ $examAttempt->max_score }}</p>
            </div>
            <div class="rounded-[1.6rem] bg-[var(--color-brand-50)] p-4">
                <p class="text-xs text-[var(--color-ink-500)]">إجابات صحيحة</p>
                <p class="mt-2 text-2xl font-bold">{{ $examAttempt->correct_answers_count }}</p>
            </div>
            <div class="rounded-[1.6rem] bg-[var(--color-brand-50)] p-4">
                <p class="text-xs text-[var(--color-ink-500)]">إجابات خاطئة</p>
                <p class="mt-2 text-2xl font-bold">{{ $wrongCount }}</p>
            </div>
            <div class="rounded-[1.6rem] bg-[var(--color-brand-50)] p-4">
                <p class="text-xs text-[var(--color-ink-500)]">إجابات متخطاة</p>
                <p class="mt-2 text-2xl font-bold">{{ $skippedCount }}</p>
            </div>
        </div>

        <div class="mt-6 flex flex-wrap gap-3">
            <a href="{{ route('student.lectures.exams.show', $examAttempt->exam) }}" class="btn-secondary">العودة إلى صفحة الاختبار</a>
            <a href="{{ route('student.mistakes.index') }}" class="btn-secondary">مراجعة مركز الأخطاء</a>
        </div>
    </section>

    <section class="mt-6 space-y-4">
        @foreach ($questionResults as $index => $result)
            <article class="panel-tight">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <p class="text-sm font-semibold text-[var(--color-brand-700)]">السؤال {{ $index + 1 }}</p>
                    <x-admin.status-badge :label="$result['is_correct'] ? 'إجابة صحيحة' : 'تحتاج مراجعة'" :tone="$result['is_correct'] ? 'success' : 'danger'" />
                </div>

                <h2 class="mt-4 text-lg font-semibold leading-9">{{ $result['question']?->prompt }}</h2>

                <div class="mt-5 grid gap-4 md:grid-cols-2">
                    <div class="rounded-[1.4rem] bg-[var(--color-brand-50)] p-4">
                        <p class="text-xs text-[var(--color-ink-500)]">إجابتك</p>
                        <p class="mt-2 font-semibold">{{ $result['selected_choice'] }}</p>
                    </div>
                    <div class="rounded-[1.4rem] bg-[var(--color-brand-50)] p-4">
                        <p class="text-xs text-[var(--color-ink-500)]">الإجابة الصحيحة</p>
                        <p class="mt-2 font-semibold">{{ $result['correct_choice'] ?? '—' }}</p>
                    </div>
                </div>

                <div class="mt-4 rounded-[1.4rem] border border-[color-mix(in_oklch,var(--color-brand-100)_88%,white)] p-4">
                    <p class="text-xs text-[var(--color-ink-500)]">التفسير</p>
                    <p class="mt-2 text-sm leading-8 text-[var(--color-ink-700)]">{{ $result['explanation'] ?: 'لا يوجد تفسير إضافي لهذا السؤال.' }}</p>
                </div>
            </article>
        @endforeach
    </section>
</x-layouts.student>
