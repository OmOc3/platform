<x-layouts.student :title="$package->product?->name_ar" :heading="$package->product?->name_ar" subheading="تفاصيل الباقة والعناصر المندرجة بداخلها مع فحص تعارضات الشراء قبل الإضافة إلى السلة.">
    <section class="grid gap-6 xl:grid-cols-[1.15fr_0.85fr]">
        <article class="panel-tight">
            <div class="flex flex-wrap items-center gap-3">
                <x-admin.status-badge :label="$package->billing_cycle_label ?: 'باقة'" />
                <x-admin.status-badge :label="$eligibility['eligible'] ? 'قابلة للشراء' : 'شراء محظور'" :tone="$eligibility['eligible'] ? 'success' : 'warning'" />
            </div>

            <p class="mt-6 text-base leading-9 text-[var(--color-ink-700)]">{{ $package->product?->description }}</p>

            <div class="mt-8 grid gap-3 sm:grid-cols-3">
                <div class="rounded-[1.4rem] bg-[var(--color-brand-50)] p-4">
                    <p class="text-xs text-[var(--color-ink-500)]">السعر</p>
                    <p class="mt-2 font-semibold">{{ number_format($package->product?->price_amount ?? 0) }} {{ $package->product?->currency }}</p>
                </div>
                <div class="rounded-[1.4rem] bg-[var(--color-brand-50)] p-4">
                    <p class="text-xs text-[var(--color-ink-500)]">عدد العناصر</p>
                    <p class="mt-2 font-semibold">{{ $package->items->count() }}</p>
                </div>
                <div class="rounded-[1.4rem] bg-[var(--color-brand-50)] p-4">
                    <p class="text-xs text-[var(--color-ink-500)]">مدة التفعيل</p>
                    <p class="mt-2 font-semibold">{{ $package->access_period_days ? $package->access_period_days.' يوم' : 'مرنة' }}</p>
                </div>
            </div>

            <div class="mt-8">
                <h2 class="text-lg font-bold">المحتوى المضمن</h2>
                <div class="mt-4 grid gap-3">
                    @foreach ($package->items as $item)
                        <div class="rounded-[1.4rem] border border-[color-mix(in_oklch,var(--color-brand-200)_70%,white)] px-4 py-4">
                            <p class="font-semibold">{{ $item->item?->title ?? $item->item_name_snapshot }}</p>
                            <p class="mt-2 text-sm text-[var(--color-ink-500)]">{{ $item->item?->short_description }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </article>

        <aside class="panel-tight">
            <p class="text-sm font-semibold text-[var(--color-brand-700)]">قرار الشراء</p>
            <p class="mt-4 text-sm leading-8 text-[var(--color-ink-700)]">{{ $eligibility['message'] }}</p>

            @if ($eligibility['overlaps'] !== [])
                <div class="mt-4 flex flex-wrap gap-2">
                    @foreach ($eligibility['overlaps'] as $title)
                        <span class="status-pill bg-[var(--color-brand-50)] text-[var(--color-brand-700)]">{{ $title }}</span>
                    @endforeach
                </div>
            @endif

            <div class="mt-6 flex flex-col gap-3">
                @if ($eligibility['eligible'] && $package->product)
                    <form method="POST" action="{{ route('student.cart.store') }}">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $package->product->id }}">
                        <button class="btn-primary">أضف الباقة إلى السلة</button>
                    </form>
                @else
                    <button type="button" class="btn-secondary !cursor-default !opacity-70">الشراء غير متاح حاليًا</button>
                @endif
                <a href="{{ route('student.packages.index') }}" class="btn-secondary">العودة إلى الباقات</a>
            </div>
        </aside>
    </section>
</x-layouts.student>
