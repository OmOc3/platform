<x-layouts.student :title="'أخطائي - '.$lecture->title" :heading="'أخطائي - '.$lecture->title" subheading="مراجعة مركزة للأسئلة الخاطئة المرتبطة بهذه المحاضرة فقط مع الإجابة النموذجية والملاحظات التوضيحية.">
    <section class="grid gap-6 xl:grid-cols-[0.72fr_1.28fr]">
        <aside class="space-y-6">
            <section class="panel-tight">
                <p class="text-sm font-semibold text-[var(--color-brand-700)]">مجموعات الأخطاء</p>
                <div class="mt-4 space-y-3">
                    @foreach ($groups as $group)
                        @php($isCurrent = $group['lecture']?->is($lecture))
                        @if ($group['lecture'])
                            <a
                                href="{{ route('student.mistakes.show', $group['lecture']) }}"
                                @class([
                                    'mistake-group-link',
                                    'mistake-group-link--active' => $isCurrent,
                                ])
                            >
                                <span>
                                    <span class="block font-semibold">{{ $group['lecture']->title }}</span>
                                    <span class="mt-1 block text-xs text-[var(--color-ink-500)]">{{ \Illuminate\Support\Carbon::parse($group['latest_at'])->diffForHumans() }}</span>
                                </span>
                                <span class="mistake-group-link__count">{{ $group['count'] }}</span>
                            </a>
                        @endif
                    @endforeach
                </div>
            </section>

            <section class="panel-tight">
                <p class="text-sm font-semibold text-[var(--color-brand-700)]">ملخص هذه المحاضرة</p>
                <div class="mt-5 grid gap-3">
                    <div class="portal-summary-card">
                        <span class="portal-summary-card__label">عدد الأخطاء</span>
                        <strong class="portal-summary-card__value">{{ $summary['count'] }}</strong>
                    </div>
                    <div class="portal-summary-card">
                        <span class="portal-summary-card__label">الدرجات المفقودة</span>
                        <strong class="portal-summary-card__value">{{ $summary['score_lost'] }}</strong>
                    </div>
                    <div class="portal-summary-card">
                        <span class="portal-summary-card__label">آخر إضافة</span>
                        <strong class="portal-summary-card__value">{{ $summary['latest_at'] ? \Illuminate\Support\Carbon::parse($summary['latest_at'])->diffForHumans() : '—' }}</strong>
                    </div>
                </div>
            </section>
        </aside>

        <section class="space-y-4">
            @foreach ($items as $index => $item)
                @php($questionImage = $item->image_path ?: data_get($item->meta, 'question_image_path'))
                @php($solutionImage = data_get($item->meta, 'solution_image_path') ?: data_get($item->meta, 'answer_image_path'))
                @php($questionImageUrl = $questionImage ? (\Illuminate\Support\Str::startsWith($questionImage, ['http://', 'https://']) ? $questionImage : asset('storage/'.$questionImage)) : null)
                @php($solutionImageUrl = $solutionImage ? (\Illuminate\Support\Str::startsWith($solutionImage, ['http://', 'https://']) ? $solutionImage : asset('storage/'.$solutionImage)) : null)

                <article class="panel-tight">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div class="flex flex-wrap items-center gap-2">
                            <x-admin.status-badge :label="'الخطأ '.$loop->iteration" tone="warning" />
                            @if ($item->exam)
                                <x-admin.status-badge :label="$item->exam->title" />
                            @endif
                        </div>
                        <span class="status-pill bg-[color-mix(in_oklch,var(--color-danger)_12%,white)] text-[color-mix(in_oklch,var(--color-danger)_70%,black)]">فقدت {{ $item->score_lost ?? 0 }} درجة</span>
                    </div>

                    <div class="mt-6 space-y-5">
                        <div>
                            <p class="text-sm font-semibold text-[var(--color-brand-700)]">السؤال</p>
                            <p class="mt-3 text-sm leading-8 text-[var(--color-ink-700)]">{{ $item->question_text }}</p>
                        </div>

                        @if ($questionImageUrl)
                            <div class="overflow-hidden rounded-[1.8rem] border border-[var(--color-border-soft)] bg-[var(--color-panel-muted)] p-3">
                                <img src="{{ $questionImageUrl }}" alt="صورة السؤال {{ $index + 1 }}" class="w-full rounded-[1.2rem] object-cover">
                            </div>
                        @endif

                        <div class="grid gap-4 lg:grid-cols-2">
                            <div class="rounded-[1.8rem] bg-[color-mix(in_oklch,var(--color-success)_8%,white)] p-5">
                                <p class="text-sm font-semibold text-[color-mix(in_oklch,var(--color-success)_70%,black)]">الإجابة الصحيحة</p>
                                <p class="mt-3 text-sm leading-8 text-[var(--color-ink-700)]">{{ $item->correct_answer_snapshot ?: '—' }}</p>
                            </div>

                            <div class="rounded-[1.8rem] bg-[var(--color-panel-muted)] p-5">
                                <p class="text-sm font-semibold text-[var(--color-brand-700)]">الإجابة النموذجية</p>
                                <p class="mt-3 text-sm leading-8 text-[var(--color-ink-700)]">{{ $item->model_answer_snapshot ?: '—' }}</p>
                            </div>
                        </div>

                        @if ($solutionImageUrl)
                            <div class="overflow-hidden rounded-[1.8rem] border border-[var(--color-border-soft)] bg-[var(--color-panel-muted)] p-3">
                                <img src="{{ $solutionImageUrl }}" alt="صورة الحل {{ $index + 1 }}" class="w-full rounded-[1.2rem] object-cover">
                            </div>
                        @endif

                        <div class="rounded-[1.8rem] border border-[color-mix(in_oklch,var(--color-violet-200)_65%,white)] bg-[color-mix(in_oklch,var(--color-violet-100)_45%,white)] p-5">
                            <p class="text-sm font-semibold text-[var(--color-violet-700)]">معلومة هامة أو الإجابة نموذجية</p>
                            <p class="mt-3 text-sm leading-8 text-[var(--color-ink-700)]">{{ $item->explanation ?: 'لا توجد ملاحظة إضافية لهذه الجزئية بعد.' }}</p>
                        </div>
                    </div>
                </article>
            @endforeach
        </section>
    </section>
</x-layouts.student>
