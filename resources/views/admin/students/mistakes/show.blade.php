<x-layouts.admin title="تفاصيل خطأ" heading="تفاصيل خطأ" subheading="عرض السؤال المسجل والشرح والإجابة النموذجية والدرجة المفقودة لهذا السجل.">
    <section class="panel-tight space-y-6">
        <div class="grid gap-4 md:grid-cols-3">
            <div>
                <p class="text-xs text-[var(--color-ink-500)]">الطالب</p>
                <p class="mt-2 font-semibold">{{ $item->student?->name }}</p>
            </div>
            <div>
                <p class="text-xs text-[var(--color-ink-500)]">المحاضرة</p>
                <p class="mt-2 font-semibold">{{ $item->lecture?->title ?? 'غير محدد' }}</p>
            </div>
            <div>
                <p class="text-xs text-[var(--color-ink-500)]">الدرجة المفقودة</p>
                <p class="mt-2 font-semibold">{{ $item->score_lost ?? 0 }}</p>
            </div>
        </div>

        <div>
            <h2 class="text-lg font-bold">السؤال</h2>
            <p class="mt-3 text-sm leading-8 text-[var(--color-ink-700)]">{{ $item->question_text }}</p>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <div class="rounded-[1.6rem] bg-[var(--color-brand-50)] p-4">
                <p class="text-xs text-[var(--color-ink-500)]">الإجابة الصحيحة</p>
                <p class="mt-2 text-sm leading-8">{{ $item->correct_answer_snapshot ?: '—' }}</p>
            </div>
            <div class="rounded-[1.6rem] bg-[var(--color-brand-50)] p-4">
                <p class="text-xs text-[var(--color-ink-500)]">النموذج المقترح</p>
                <p class="mt-2 text-sm leading-8">{{ $item->model_answer_snapshot ?: '—' }}</p>
            </div>
        </div>

        @if ($item->explanation)
            <div>
                <h3 class="text-sm font-semibold text-[var(--color-brand-700)]">الشرح</h3>
                <p class="mt-2 text-sm leading-8 text-[var(--color-ink-700)]">{{ $item->explanation }}</p>
            </div>
        @endif
    </section>
</x-layouts.admin>
