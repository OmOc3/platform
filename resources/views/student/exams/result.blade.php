<x-layouts.student :title="'نتيجة: '.$examAttempt->exam->title" :heading="'نتيجة '.$examAttempt->exam->title" subheading="مراجعة النتيجة سؤالًا بسؤال مع الاختيارات الصحيحة، الشرح، وأي صور مرفقة للحل.">
    <section class="space-y-6">
        <section class="panel-tight">
            <div class="flex flex-wrap items-center gap-3">
                <x-admin.status-badge :label="$examAttempt->status->label()" :tone="$examAttempt->status->tone()" />
                @if (data_get($examAttempt->result_meta, 'submitted_by_timer'))
                    <x-admin.status-badge label="تم الإرسال بانتهاء الوقت" tone="warning" />
                @endif
                <x-admin.status-badge :label="'المحاولة '.$examAttempt->attempt_number" />
            </div>

            <div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-5">
                <div class="portal-summary-card">
                    <span class="portal-summary-card__label">النتيجة</span>
                    <strong class="portal-summary-card__value">{{ $examAttempt->total_score }}/{{ $examAttempt->max_score }}</strong>
                </div>
                <div class="portal-summary-card">
                    <span class="portal-summary-card__label">صحيح</span>
                    <strong class="portal-summary-card__value">{{ $examAttempt->correct_answers_count }}</strong>
                </div>
                <div class="portal-summary-card">
                    <span class="portal-summary-card__label">خطأ</span>
                    <strong class="portal-summary-card__value">{{ $wrongCount }}</strong>
                </div>
                <div class="portal-summary-card">
                    <span class="portal-summary-card__label">متخطى</span>
                    <strong class="portal-summary-card__value">{{ $skippedCount }}</strong>
                </div>
                <div class="portal-summary-card">
                    <span class="portal-summary-card__label">وقت الإرسال</span>
                    <strong class="portal-summary-card__value">{{ optional($examAttempt->graded_at)->format('Y/m/d H:i') ?: '—' }}</strong>
                </div>
            </div>

            <div class="mt-6 flex flex-wrap gap-3">
                <a href="{{ route('student.lectures.exams.show', $examAttempt->exam) }}" class="btn-secondary">العودة إلى صفحة الاختبار</a>
                <a href="{{ route('student.mistakes.index') }}" class="btn-secondary">الانتقال إلى أخطائي</a>
            </div>
        </section>

        <section class="grid gap-6 xl:grid-cols-[0.34fr_1.66fr]">
            <aside class="panel-tight">
                <p class="section-kicker">التنقل بين الأسئلة</p>
                <div class="mt-4 grid grid-cols-4 gap-2">
                    @foreach ($questionResults as $result)
                        <a
                            href="#question-{{ $result['number'] }}"
                            @class([
                                'exam-question-nav',
                                'exam-question-nav--correct' => $result['is_correct'],
                                'exam-question-nav--wrong' => ! $result['is_correct'],
                            ])
                        >
                            {{ $result['number'] }}
                        </a>
                    @endforeach
                </div>
            </aside>

            <div class="space-y-4">
                @foreach ($questionResults as $result)
                    @php($questionImage = $result['question_image'])
                    @php($solutionImage = $result['solution_image'])
                    @php($questionImageUrl = $questionImage ? (\Illuminate\Support\Str::startsWith($questionImage, ['http://', 'https://']) ? $questionImage : asset('storage/'.$questionImage)) : null)
                    @php($solutionImageUrl = $solutionImage ? (\Illuminate\Support\Str::startsWith($solutionImage, ['http://', 'https://']) ? $solutionImage : asset('storage/'.$solutionImage)) : null)

                    <article id="question-{{ $result['number'] }}" class="panel-tight scroll-mt-28">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div class="flex flex-wrap items-center gap-2">
                                <x-admin.status-badge :label="'السؤال '.$result['number']" />
                                <x-admin.status-badge :label="$result['is_correct'] ? 'إجابة صحيحة' : 'إجابة تحتاج مراجعة'" :tone="$result['is_correct'] ? 'success' : 'danger'" />
                            </div>
                            <span class="status-pill status-pill--brand">{{ $result['awarded_score'] }}/{{ $result['max_score'] }}</span>
                        </div>

                        <h2 class="mt-4 text-lg font-semibold leading-9">{{ $result['question']?->prompt }}</h2>

                        @if ($questionImageUrl)
                            <div class="mt-5 overflow-hidden rounded-[1.8rem] border border-[var(--color-border-soft)] bg-[var(--color-panel-muted)] p-3">
                                <img src="{{ $questionImageUrl }}" alt="صورة السؤال {{ $result['number'] }}" class="w-full rounded-[1.2rem] object-cover" loading="lazy" decoding="async">
                            </div>
                        @endif

                        @if ($result['choices'] !== [])
                            <div class="mt-5 grid gap-3">
                                @foreach ($result['choices'] as $choice)
                                    <div
                                        @class([
                                            'exam-choice',
                                            'exam-choice--correct' => $choice['is_correct'],
                                            'exam-choice--selected' => $choice['is_selected'] && ! $choice['is_correct'],
                                        ])
                                    >
                                        <span class="text-sm font-semibold">{{ $choice['content'] }}</span>
                                        <div class="flex flex-wrap items-center gap-2">
                                            @if ($choice['is_correct'])
                                                <x-admin.status-badge label="الصحيح" tone="success" />
                                            @endif
                                            @if ($choice['is_selected'])
                                                <x-admin.status-badge :label="$choice['is_correct'] ? 'اختيارك' : 'إجابتك'" :tone="$choice['is_correct'] ? 'success' : 'warning'" />
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <div class="mt-5 grid gap-4 lg:grid-cols-2">
                            <div class="rounded-[1.8rem] bg-[var(--color-panel-muted)] p-5">
                                <p class="text-sm font-semibold text-[var(--color-ink-900)]">إجابتك</p>
                                <p class="mt-3 text-sm leading-8 text-[var(--color-ink-700)]">{{ $result['selected_choice'] }}</p>
                            </div>

                            <div class="surface-tone surface-tone--success rounded-[1.8rem] p-5">
                                <p class="text-sm font-semibold">الإجابة الصحيحة</p>
                                <p class="mt-3 text-sm leading-8 text-[var(--color-ink-700)]">{{ $result['correct_choice'] ?? '—' }}</p>
                            </div>
                        </div>

                        @if ($solutionImageUrl)
                            <div class="mt-5 overflow-hidden rounded-[1.8rem] border border-[var(--color-border-soft)] bg-[var(--color-panel-muted)] p-3">
                                <img src="{{ $solutionImageUrl }}" alt="صورة الحل {{ $result['number'] }}" class="w-full rounded-[1.2rem] object-cover" loading="lazy" decoding="async">
                            </div>
                        @endif

                        <div class="mt-5 rounded-[1.8rem] border border-[var(--color-border-soft)] bg-[var(--color-panel-muted)] p-5">
                            <p class="text-sm font-semibold text-[var(--color-brand-700)]">معلومة هامة أو الإجابة نموذجية</p>
                            <p class="mt-3 text-sm leading-8 text-[var(--color-ink-700)]">{{ $result['important_note'] ?: $result['explanation'] ?: 'لا يوجد شرح إضافي لهذا السؤال.' }}</p>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>
    </section>
</x-layouts.student>
