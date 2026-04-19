<x-layouts.student title="أخطائي" heading="مركز الأخطاء" subheading="راجع الأخطاء المجمعة حسب المحاضرة وحدد أين تخسر الدرجات ولماذا.">
    <section class="space-y-6">
        @if ($groups->isEmpty())
            <x-student.empty-state title="لا توجد أخطاء مسجلة بعد" description="عند ربط الاختبارات والمحاولات لاحقًا أو إضافة بيانات مراجعة، سيظهر سجل الأخطاء هنا." />
        @else
            <div class="grid gap-4 xl:grid-cols-2">
                @foreach ($groups as $group)
                    <article class="panel-tight">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <p class="text-sm font-semibold text-[var(--color-brand-700)]">المحاضرة</p>
                                <h2 class="mt-2 text-lg font-bold">{{ $group['lecture']?->title ?? 'محتوى غير محدد' }}</h2>
                            </div>
                            <div class="text-left">
                                <p class="text-xs text-[var(--color-ink-500)]">عدد الأخطاء</p>
                                <p class="mt-2 text-2xl font-bold">{{ $group['count'] }}</p>
                            </div>
                        </div>

                        <div class="mt-5 flex flex-wrap gap-3 text-sm text-[var(--color-ink-700)]">
                            <span>إجمالي الدرجات المفقودة: {{ $group['score_lost'] }}</span>
                            <span>آخر تحديث: {{ \Illuminate\Support\Carbon::parse($group['latest_at'])->diffForHumans() }}</span>
                        </div>

                        @if ($group['lecture'])
                            <div class="mt-6">
                                <a href="{{ route('student.mistakes.show', $group['lecture']) }}" class="btn-primary">افتح التفاصيل</a>
                            </div>
                        @endif
                    </article>
                @endforeach
            </div>
        @endif
    </section>
</x-layouts.student>
