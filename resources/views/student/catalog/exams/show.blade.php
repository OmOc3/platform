<x-layouts.student :title="$exam->title" :heading="$exam->title" subheading="وصف سريع للاختبار وحالة الوصول الحالية قبل تنفيذ مرحلة المحاولات الكاملة لاحقًا.">
    <section class="grid gap-6 xl:grid-cols-[1.1fr_0.9fr]">
        <article class="panel-tight">
            <div class="flex flex-wrap items-center gap-3">
                <x-student.access-state :access="$access" />
                @if ($exam->lecture)
                    <x-admin.status-badge label="مرتبط بمحاضرة" />
                @endif
            </div>

            <p class="mt-4 text-sm text-[var(--color-ink-500)]">
                {{ $exam->grade?->name_ar }}{{ $exam->track ? ' / '.$exam->track->name_ar : '' }}
            </p>
            <p class="mt-6 text-base leading-9 text-[var(--color-ink-700)]">{{ $exam->long_description ?: $exam->short_description }}</p>

            <dl class="mt-8 grid gap-4 sm:grid-cols-3">
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
            </dl>
        </article>

        <aside class="panel-tight">
            <p class="text-sm font-semibold text-[var(--color-brand-700)]">ما الذي يمكن فعله الآن؟</p>
            <p class="mt-4 text-sm leading-8 text-[var(--color-ink-700)]">{{ $access['reason'] ?: 'الاختبار جاهز للفتح عند توفر الوصول.' }}</p>

            <div class="mt-6 flex flex-col gap-3">
                @if (in_array($access['state']->value, ['open', 'free', 'owned_via_entitlement'], true))
                    <button type="button" class="btn-primary">فتح صفحة الاختبار</button>
                @elseif ($exam->lecture)
                    <a href="{{ route('student.lectures.show', $exam->lecture) }}" class="btn-primary">راجع المحاضرة المرتبطة</a>
                @else
                    <a href="{{ route('student.lectures.index', ['tab' => 'exam']) }}" class="btn-secondary">العودة إلى الاختبارات</a>
                @endif
            </div>
        </aside>
    </section>
</x-layouts.student>
