<x-layouts.student :title="$lecture->title" :heading="'أخطاء '.$lecture->title" subheading="تفصيل لكل سؤال تم تسجيله ضمن هذه المحاضرة مع الإجابة النموذجية والشرح والدرجة المفقودة.">
    <section class="space-y-4">
        @foreach ($items as $item)
            <article class="panel-tight">
                <div class="flex flex-wrap items-center gap-3">
                    <x-admin.status-badge label="خطأ مسجل" tone="warning" />
                    @if ($item->exam)
                        <x-admin.status-badge :label="'من '.$item->exam->title" />
                    @endif
                </div>

                <div class="mt-5 grid gap-5 xl:grid-cols-[1fr_0.9fr]">
                    <div>
                        <h2 class="text-lg font-bold">السؤال</h2>
                        <p class="mt-3 text-sm leading-8 text-[var(--color-ink-700)]">{{ $item->question_text }}</p>

                        @if ($item->explanation)
                            <h3 class="mt-6 text-sm font-semibold text-[var(--color-brand-700)]">الشرح</h3>
                            <p class="mt-2 text-sm leading-8 text-[var(--color-ink-700)]">{{ $item->explanation }}</p>
                        @endif
                    </div>

                    <div class="space-y-4">
                        <div class="rounded-[1.6rem] bg-[var(--color-brand-50)] p-4">
                            <p class="text-xs text-[var(--color-ink-500)]">الإجابة الصحيحة</p>
                            <p class="mt-2 text-sm leading-8">{{ $item->correct_answer_snapshot ?: '—' }}</p>
                        </div>
                        <div class="rounded-[1.6rem] bg-[var(--color-brand-50)] p-4">
                            <p class="text-xs text-[var(--color-ink-500)]">النموذج المقترح</p>
                            <p class="mt-2 text-sm leading-8">{{ $item->model_answer_snapshot ?: '—' }}</p>
                        </div>
                        <div class="rounded-[1.6rem] bg-[var(--color-brand-50)] p-4">
                            <p class="text-xs text-[var(--color-ink-500)]">الدرجة المفقودة</p>
                            <p class="mt-2 font-semibold">{{ $item->score_lost ?? 0 }}</p>
                        </div>
                    </div>
                </div>
            </article>
        @endforeach
    </section>
</x-layouts.student>
