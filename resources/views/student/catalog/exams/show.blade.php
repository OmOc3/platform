<x-layouts.student :title="$exam->title" :heading="$exam->title" :subheading="$exam->short_description ?: 'صفحة تمهيدية لبدء الاختبار أو استكمال آخر محاولة محفوظة.'">
    <section class="grid gap-6 xl:grid-cols-[1.1fr_0.9fr]">
        <article class="panel-tight">
            <div class="flex flex-wrap items-center gap-3">
                <x-student.access-state :access="$access" />
                <x-admin.status-badge :label="'عدد المحاولات '.$attemptsCount.'/'.$maxAttempts" tone="neutral" />
                @if ($currentAttempt)
                    <x-admin.status-badge label="محاولة جارية" tone="warning" />
                @endif
            </div>

            <p class="mt-4 text-sm text-[var(--color-ink-500)]">
                {{ $exam->grade?->name_ar }}{{ $exam->track ? ' / '.$exam->track->name_ar : '' }}
            </p>

            <p class="mt-6 text-base leading-9 text-[var(--color-ink-700)]">
                {{ $exam->long_description ?: $exam->short_description }}
            </p>

            <dl class="mt-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-[1.6rem] bg-[var(--color-brand-50)] p-4">
                    <dt class="text-xs text-[var(--color-ink-500)]">المدة</dt>
                    <dd class="mt-2 font-semibold">{{ $exam->duration_minutes ? $exam->duration_minutes.' دقيقة' : 'غير محدد' }}</dd>
                </div>
                <div class="rounded-[1.6rem] bg-[var(--color-brand-50)] p-4">
                    <dt class="text-xs text-[var(--color-ink-500)]">عدد الأسئلة</dt>
                    <dd class="mt-2 font-semibold">{{ $exam->question_count ?: '—' }}</dd>
                </div>
                <div class="rounded-[1.6rem] bg-[var(--color-brand-50)] p-4">
                    <dt class="text-xs text-[var(--color-ink-500)]">المحاضرة المرتبطة</dt>
                    <dd class="mt-2 font-semibold">{{ $exam->lecture?->title ?? 'اختبار مستقل' }}</dd>
                </div>
                <div class="rounded-[1.6rem] bg-[var(--color-brand-50)] p-4">
                    <dt class="text-xs text-[var(--color-ink-500)]">الحد الأقصى للمحاولات</dt>
                    <dd class="mt-2 font-semibold">{{ $maxAttempts }}</dd>
                </div>
            </dl>

            @if ($latestAttempt)
                <section class="mt-8 rounded-[1.8rem] border border-[color-mix(in_oklch,var(--color-success)_22%,white)] bg-[color-mix(in_oklch,var(--color-success)_8%,white)] p-5">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <p class="text-sm font-semibold text-[var(--color-success)]">آخر نتيجة مسجلة</p>
                            <p class="mt-2 text-sm text-[var(--color-ink-600)]">
                                المحاولة رقم {{ $latestAttempt->attempt_number }} بتاريخ {{ optional($latestAttempt->graded_at)->format('Y/m/d H:i') }}
                            </p>
                        </div>
                        <a href="{{ route('student.exam-attempts.result', $latestAttempt) }}" class="btn-secondary">استعراض النتيجة</a>
                    </div>

                    <div class="mt-4 grid gap-3 sm:grid-cols-3">
                        <div class="rounded-[1.4rem] bg-white/70 p-4">
                            <p class="text-xs text-[var(--color-ink-500)]">الدرجة</p>
                            <p class="mt-2 text-xl font-bold">{{ $latestAttempt->total_score }}/{{ $latestAttempt->max_score }}</p>
                        </div>
                        <div class="rounded-[1.4rem] bg-white/70 p-4">
                            <p class="text-xs text-[var(--color-ink-500)]">إجابات صحيحة</p>
                            <p class="mt-2 text-xl font-bold">{{ $latestAttempt->correct_answers_count }}</p>
                        </div>
                        <div class="rounded-[1.4rem] bg-white/70 p-4">
                            <p class="text-xs text-[var(--color-ink-500)]">إجابات متخطاة</p>
                            <p class="mt-2 text-xl font-bold">{{ data_get($latestAttempt->result_meta, 'skipped_count', 0) }}</p>
                        </div>
                    </div>
                </section>
            @endif
        </article>

        <aside class="panel-tight">
            <p class="text-sm font-semibold text-[var(--color-brand-700)]">حالة الاختبار الآن</p>
            <p class="mt-4 text-sm leading-8 text-[var(--color-ink-700)]">{{ $attemptMessage }}</p>

            <div class="mt-6 flex flex-col gap-3">
                @if ($currentAttempt)
                    <a href="{{ route('student.exam-attempts.show', $currentAttempt) }}" class="btn-primary">استكمال المحاولة الحالية</a>
                @elseif ($canStartAttempt)
                    <form method="POST" action="{{ route('student.exam-attempts.start', $exam) }}">
                        @csrf
                        <button type="submit" class="btn-primary w-full">ابدأ الاختبار الآن</button>
                    </form>
                @elseif ($latestAttempt)
                    <a href="{{ route('student.exam-attempts.result', $latestAttempt) }}" class="btn-secondary">عرض آخر نتيجة</a>
                @endif

                @if ($exam->lecture)
                    <a href="{{ route('student.lectures.show', $exam->lecture) }}" class="btn-secondary">العودة إلى المحاضرة المرتبطة</a>
                @else
                    <a href="{{ route('student.lectures.index', ['tab' => 'exam']) }}" class="btn-secondary">كل الاختبارات</a>
                @endif
            </div>
        </aside>
    </section>
</x-layouts.student>
