<x-layouts.student :title="$lecture->title" :heading="$lecture->title" subheading="صفحة تفصيلية للمحتوى مع حالة الوصول الحالية والبيانات الأكاديمية المرتبطة به.">
    <section class="grid gap-6 xl:grid-cols-[1.2fr_0.8fr]">
        <article class="panel-tight">
            <div class="flex flex-wrap items-center gap-3">
                <x-student.access-state :access="$access" />
                <x-admin.status-badge :label="$lecture->type->value === 'review' ? 'مراجعة' : 'محاضرة'" />
            </div>

            <p class="mt-4 text-sm text-[var(--color-ink-500)]">
                {{ $lecture->grade?->name_ar }}{{ $lecture->track ? ' / '.$lecture->track->name_ar : '' }}
            </p>
            <p class="mt-6 text-base leading-9 text-[var(--color-ink-700)]">{{ $lecture->long_description ?: $lecture->short_description }}</p>

            <dl class="mt-8 grid gap-4 sm:grid-cols-2">
                <div class="rounded-[1.6rem] bg-[var(--color-brand-50)] p-4">
                    <dt class="text-xs text-[var(--color-ink-500)]">قسم المنهج</dt>
                    <dd class="mt-2 font-semibold">{{ $lecture->curriculumSection?->name_ar ?? 'عام' }}</dd>
                </div>
                <div class="rounded-[1.6rem] bg-[var(--color-brand-50)] p-4">
                    <dt class="text-xs text-[var(--color-ink-500)]">قسم المحاضرات</dt>
                    <dd class="mt-2 font-semibold">{{ $lecture->lectureSection?->name_ar ?? 'عام' }}</dd>
                </div>
                <div class="rounded-[1.6rem] bg-[var(--color-brand-50)] p-4">
                    <dt class="text-xs text-[var(--color-ink-500)]">المدة</dt>
                    <dd class="mt-2 font-semibold">{{ $lecture->duration_minutes ? $lecture->duration_minutes.' دقيقة' : 'غير محدد' }}</dd>
                </div>
                <div class="rounded-[1.6rem] bg-[var(--color-brand-50)] p-4">
                    <dt class="text-xs text-[var(--color-ink-500)]">السعر</dt>
                    <dd class="mt-2 font-semibold">{{ number_format($lecture->price_amount) }} {{ $lecture->currency }}</dd>
                </div>
            </dl>
        </article>

        <aside class="panel-tight">
            <p class="text-sm font-semibold text-[var(--color-brand-700)]">حالة الوصول</p>
            <div class="mt-4"><x-student.access-state :access="$access" /></div>
            @if ($access['reason'])
                <p class="mt-4 text-sm leading-8 text-[var(--color-ink-700)]">{{ $access['reason'] }}</p>
            @endif

            <div class="mt-6 flex flex-col gap-3">
                @if (in_array($access['state']->value, ['open', 'free', 'owned_via_entitlement'], true))
                    <button type="button" class="btn-primary">افتح المحتوى</button>
                @elseif ($access['state']->value === 'buy' && $lecture->product)
                    <form method="POST" action="{{ route('student.cart.store') }}">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $lecture->product->id }}">
                        <button class="btn-primary">أضف إلى السلة</button>
                    </form>
                @elseif ($access['state']->value === 'included_in_package')
                    <a href="{{ route('student.packages.index') }}" class="btn-primary">استعرض الباقات المرتبطة</a>
                @endif
                <a href="{{ route('student.lectures.index', ['tab' => $lecture->type->value === 'review' ? 'review' : 'lecture']) }}" class="btn-secondary">العودة إلى الكتالوج</a>
            </div>
        </aside>
    </section>
</x-layouts.student>
