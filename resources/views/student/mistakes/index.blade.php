<x-layouts.student title="أخطائي" heading="أخطائي" subheading="دفتر مراجعة يجمع الأخطاء حسب المحاضرة حتى تراجع ما خسرته ولماذا.">
    @php($totalMistakes = $groups->sum('count'))
    @php($totalLost = $groups->sum('score_lost'))

    <section class="space-y-6">
        <section class="panel-tight">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="text-sm font-semibold text-[var(--color-brand-700)]">دفتر الأخطاء</p>
                    <h2 class="mt-2 text-2xl font-bold lg:text-3xl">راجع فقط الأسئلة التي أخطأت فيها.</h2>
                    <p class="mt-3 max-w-3xl text-sm leading-8 text-[var(--color-ink-700)]">
                        كل مجموعة مرتبطة بمحاضرة محددة، وبداخلها عدد الأخطاء وإجمالي الدرجات المفقودة وآخر وقت تمت فيه إضافة خطأ جديد.
                    </p>
                </div>
                @if ($groups->isNotEmpty() && $groups->first()['lecture'])
                    <a href="{{ route('student.mistakes.show', $groups->first()['lecture']) }}" class="btn-primary">ابدأ المراجعة</a>
                @endif
            </div>

            <div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <x-student.summary-card label="المحاضرات" :value="$groups->count()" description="عدد المجموعات المسجلة داخل الدفتر" />
                <x-student.summary-card label="إجمالي الأخطاء" :value="$totalMistakes" description="كل الأسئلة الخاطئة المجمعة" />
                <x-student.summary-card label="الدرجات المفقودة" :value="$totalLost" description="إجمالي ما فُقد من درجات" />
                <x-student.summary-card label="حالة المراجعة" :value="$totalMistakes > 0 ? 'جاهز' : 'فارغ'" description="يظهر هنا السجل الفعلي بدل أي placeholder" />
            </div>
        </section>

        @if ($groups->isEmpty())
            <x-student.empty-state title="لا توجد أخطاء مسجلة بعد" description="عند ظهور أخطاء جديدة من محاولات الامتحانات أو المراجعة ستظهر هنا مباشرة." />
        @else
            <div class="grid gap-4 xl:grid-cols-2">
                @foreach ($groups as $group)
                    <article class="surface-card rounded-[2rem] p-5">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-ink-500)]">المحاضرة</p>
                                <h2 class="mt-3 text-xl font-bold">{{ $group['lecture']?->title ?? 'محتوى غير محدد' }}</h2>
                            </div>
                            <span class="status-pill status-pill--danger">{{ $group['count'] }} خطأ</span>
                        </div>

                        <div class="mt-5 grid gap-3 sm:grid-cols-2">
                            <div class="rounded-[1.4rem] bg-[var(--color-panel-muted)] p-4">
                                <p class="text-xs text-[var(--color-ink-500)]">إجمالي الدرجات المفقودة</p>
                                <p class="mt-2 font-semibold">{{ $group['score_lost'] }}</p>
                            </div>
                            <div class="rounded-[1.4rem] bg-[var(--color-panel-muted)] p-4">
                                <p class="text-xs text-[var(--color-ink-500)]">آخر تحديث</p>
                                <p class="mt-2 font-semibold">{{ \Illuminate\Support\Carbon::parse($group['latest_at'])->diffForHumans() }}</p>
                            </div>
                        </div>

                        @if ($group['lecture'])
                            <a href="{{ route('student.mistakes.show', $group['lecture']) }}" class="btn-primary mt-5 w-full">فتح دفتر المحاضرة</a>
                        @endif
                    </article>
                @endforeach
            </div>
        @endif
    </section>
</x-layouts.student>
