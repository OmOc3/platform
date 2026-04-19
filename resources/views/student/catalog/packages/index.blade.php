<x-layouts.student title="الباقات" heading="الباقات التعليمية" subheading="قارن بين الباقات المتاحة، العناصر المضمنة، وحالة الأهلية للشراء قبل إضافتها إلى السلة.">
    <section class="space-y-6">
        <div class="grid gap-4 xl:grid-cols-2">
            @forelse ($packages as $row)
                @php($package = $row['package'])
                @php($eligibility = $row['eligibility'])
                <article class="panel-tight">
                    <div class="flex flex-wrap items-center gap-3">
                        <x-admin.status-badge :label="$package->billing_cycle_label ?: 'باقة رقمية'" />
                        <x-admin.status-badge :label="$eligibility['eligible'] ? 'قابلة للشراء' : 'محظورة حاليًا'" :tone="$eligibility['eligible'] ? 'success' : 'warning'" />
                    </div>

                    <h2 class="mt-4 text-xl font-bold">{{ $package->product?->name_ar }}</h2>
                    <p class="mt-3 text-sm leading-8 text-[var(--color-ink-700)]">{{ $package->product?->description }}</p>

                    <div class="mt-6 grid gap-3 sm:grid-cols-3">
                        <div class="rounded-[1.4rem] bg-[var(--color-brand-50)] p-4">
                            <p class="text-xs text-[var(--color-ink-500)]">السعر</p>
                            <p class="mt-2 font-semibold">{{ number_format($package->product?->price_amount ?? 0) }} {{ $package->product?->currency }}</p>
                        </div>
                        <div class="rounded-[1.4rem] bg-[var(--color-brand-50)] p-4">
                            <p class="text-xs text-[var(--color-ink-500)]">العناصر</p>
                            <p class="mt-2 font-semibold">{{ $package->items->count() }}</p>
                        </div>
                        <div class="rounded-[1.4rem] bg-[var(--color-brand-50)] p-4">
                            <p class="text-xs text-[var(--color-ink-500)]">مدة التفعيل</p>
                            <p class="mt-2 font-semibold">{{ $package->access_period_days ? $package->access_period_days.' يوم' : 'حسب الباقة' }}</p>
                        </div>
                    </div>

                    <p class="mt-5 text-sm leading-8 text-[var(--color-ink-700)]">{{ $eligibility['message'] }}</p>
                    @if ($eligibility['overlaps'] !== [])
                        <div class="mt-3 flex flex-wrap gap-2">
                            @foreach ($eligibility['overlaps'] as $title)
                                <span class="status-pill bg-[var(--color-brand-50)] text-[var(--color-brand-700)]">{{ $title }}</span>
                            @endforeach
                        </div>
                    @endif

                    <div class="mt-6 flex flex-wrap gap-3">
                        <a href="{{ route('student.packages.show', $package) }}" class="btn-primary">عرض التفاصيل</a>
                        @if ($eligibility['eligible'] && $package->product)
                            <form method="POST" action="{{ route('student.cart.store') }}">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $package->product->id }}">
                                <button class="btn-secondary">أضف إلى السلة</button>
                            </form>
                        @endif
                    </div>
                </article>
            @empty
                <x-student.empty-state title="لا توجد باقات منشورة" description="عند تفعيل باقات جديدة ستظهر هنا تلقائيًا." />
            @endforelse
        </div>

        <div class="px-2">
            {{ $packages->links() }}
        </div>
    </section>
</x-layouts.student>
