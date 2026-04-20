<x-layouts.student :title="'محاولة: '.$examAttempt->exam->title" :heading="$examAttempt->exam->title" subheading="أجب عن الأسئلة التالية ثم احفظ التقدم أو أرسل الاختبار عند الانتهاء.">
    <section class="grid gap-6 xl:grid-cols-[0.75fr_1.25fr]">
        <aside class="panel-tight h-fit xl:sticky xl:top-6">
            <div class="space-y-4">
                <div>
                    <p class="text-sm font-semibold text-[var(--color-brand-700)]">بيانات المحاولة</p>
                    <p class="mt-3 text-sm text-[var(--color-ink-600)]">المحاولة رقم {{ $examAttempt->attempt_number }}</p>
                </div>

                <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-1">
                    <div class="rounded-[1.4rem] bg-[var(--color-brand-50)] p-4">
                        <p class="text-xs text-[var(--color-ink-500)]">الأسئلة</p>
                        <p class="mt-2 font-semibold">{{ $examAttempt->total_questions }}</p>
                    </div>
                    <div class="rounded-[1.4rem] bg-[var(--color-brand-50)] p-4">
                        <p class="text-xs text-[var(--color-ink-500)]">الوقت المتبقي</p>
                        <p class="mt-2 font-semibold" data-timer-display>{{ $expiresAt ? $expiresAt->diffForHumans(now(), true, false, 2) : 'غير محدد' }}</p>
                    </div>
                </div>

                <div class="surface-outline surface-outline--brand rounded-[1.4rem] p-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-ink-500)]">التنقل بين الأسئلة</p>
                    <div class="mt-3 flex flex-wrap gap-2">
                        @foreach ($examAttempt->exam->examQuestions as $index => $examQuestion)
                            <a href="#question-{{ $examQuestion->question_id }}" class="rounded-full bg-[var(--color-brand-50)] px-3 py-2 text-sm font-semibold text-[var(--color-ink-700)]">
                                {{ $index + 1 }}
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </aside>

        <article class="panel-tight">
            <form id="exam-attempt-form" method="POST" action="{{ route('student.exam-attempts.save', $examAttempt) }}" class="space-y-6">
                @csrf

                @error('answers')
                    <p class="surface-tone surface-tone--danger rounded-2xl px-4 py-3 text-sm font-medium">{{ $message }}</p>
                @enderror

                @foreach ($examAttempt->exam->examQuestions as $index => $examQuestion)
                    @php
                        $question = $examQuestion->question;
                        $selectedAnswer = old('answers.'.$question->id, $answerMap[$question->id] ?? null);
                    @endphp
                    <section id="question-{{ $question->id }}" class="surface-outline surface-outline--brand rounded-[1.8rem] p-5">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <p class="text-sm font-semibold text-[var(--color-brand-700)]">السؤال {{ $index + 1 }}</p>
                            <x-admin.status-badge :label="$examQuestion->max_score.' درجة'" tone="neutral" />
                        </div>

                        <h2 class="mt-4 text-lg font-semibold leading-9">{{ $question->prompt }}</h2>

                        <div class="mt-5 space-y-3">
                            @foreach ($question->choices->where('is_active', true)->sortBy('sort_order') as $choice)
                                <label class="flex cursor-pointer items-start gap-3 rounded-[1.4rem] bg-[var(--color-brand-50)] px-4 py-3">
                                    <input type="radio"
                                           name="answers[{{ $question->id }}]"
                                           value="{{ $choice->id }}"
                                           class="mt-1 h-4 w-4"
                                           @checked((string) $selectedAnswer === (string) $choice->id)>
                                    <span class="text-sm leading-8 text-[var(--color-ink-700)]">{{ $choice->content }}</span>
                                </label>
                            @endforeach
                        </div>
                    </section>
                @endforeach

                <div class="flex flex-wrap gap-3 border-t border-[var(--color-border-soft)] pt-5">
                    <button type="submit" class="btn-secondary">حفظ التقدم</button>
                    <button type="submit"
                            class="btn-primary"
                            formaction="{{ route('student.exam-attempts.submit', $examAttempt) }}"
                            onclick="return confirm('هل تريد إرسال الاختبار الآن؟')">
                        إرسال الاختبار
                    </button>
                </div>
            </form>
        </article>
    </section>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const timerDisplay = document.querySelector('[data-timer-display]');
                const attemptForm = document.getElementById('exam-attempt-form');
                const submitAction = @json(route('student.exam-attempts.submit', $examAttempt));
                const expiresAt = {{ $expiresAt?->getTimestampMs() ?? 'null' }};

                if (! timerDisplay || ! attemptForm || ! expiresAt) {
                    return;
                }

                const formatDuration = (milliseconds) => {
                    const totalSeconds = Math.max(0, Math.floor(milliseconds / 1000));
                    const hours = Math.floor(totalSeconds / 3600);
                    const minutes = Math.floor((totalSeconds % 3600) / 60);
                    const seconds = totalSeconds % 60;

                    return [hours, minutes, seconds]
                        .map((value) => String(value).padStart(2, '0'))
                        .join(':');
                };

                const tick = () => {
                    const remaining = expiresAt - Date.now();

                    if (remaining <= 0) {
                        timerDisplay.textContent = '00:00:00';
                        attemptForm.action = submitAction;
                        attemptForm.submit();
                        return;
                    }

                    timerDisplay.textContent = formatDuration(remaining);
                };

                tick();
                window.setInterval(tick, 1000);
            });
        </script>
    @endpush
</x-layouts.student>
