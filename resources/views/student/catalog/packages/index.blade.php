<x-layouts.student title="الباقات" heading="الباقات التعليمية" subheading="قارن بين الباقات 3 شهور، الباقات الشهرية، والعروض الخاصة مع حالة الشراء الفعلية قبل الإضافة إلى السلة.">
    @php($groupedPackages = collect($packages->items())->groupBy('group'))
    @php($groupMeta = [
        'quarterly' => ['title' => 'باقات 3 شهور', 'description' => 'عروض ممتدة لمن يريد خطة مذاكرة مستقرة على فترة أطول.'],
        'monthly' => ['title' => 'الباقات الشهرية', 'description' => 'باقات أقصر مناسبة للمتابعة السريعة أو شراء الشهر الحالي فقط.'],
        'special' => ['title' => 'العروض الخاصة', 'description' => 'المعسكرات والعروض الموسمية وما يتطلب خطة مراجعة مركزة.'],
    ])

    <section class="space-y-6">
        <section class="panel-tight">
            <div class="grid gap-4 lg:grid-cols-[1.1fr_0.9fr] lg:items-center">
                <div>
                    <p class="section-kicker">كتالوج العروض</p>
                    <h2 class="mt-2 text-2xl font-bold lg:text-3xl">كل باقة تظهر هنا بحالتها الحقيقية قبل الشراء.</h2>
                    <p class="mt-3 text-sm leading-8 text-[var(--color-ink-700)]">
                        إذا كنت اشتريت العرض بالفعل أو لديك محاضرات متداخلة معه، ستظهر الرسالة المناسبة مباشرة. وإذا كانت الباقة قابلة للشراء فستضيفها إلى السلة من نفس الشاشة.
                    </p>
                </div>

                <div class="grid gap-3 sm:grid-cols-3">
                    <div class="portal-shell-meta">
                        <span class="portal-shell-meta__label">إجمالي الباقات</span>
                        <strong class="portal-shell-meta__value">{{ $packages->total() }}</strong>
                    </div>
                    <div class="portal-shell-meta">
                        <span class="portal-shell-meta__label">العروض 3 شهور</span>
                        <strong class="portal-shell-meta__value">{{ $groupedPackages->get('quarterly', collect())->count() }}</strong>
                    </div>
                    <div class="portal-shell-meta">
                        <span class="portal-shell-meta__label">العروض الشهرية</span>
                        <strong class="portal-shell-meta__value">{{ $groupedPackages->get('monthly', collect())->count() }}</strong>
                    </div>
                </div>
            </div>
        </section>

        @if (collect($packages->items())->isEmpty())
            <x-student.empty-state title="لا توجد باقات منشورة" description="عند تفعيل باقات جديدة من لوحة الإدارة ستظهر هنا تلقائيًا." />
        @else
            @foreach ($groupMeta as $groupKey => $meta)
                @continue(! $groupedPackages->has($groupKey))

                <section class="panel-tight">
                    <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
                        <div>
                            <p class="text-sm font-semibold text-[var(--color-brand-700)]">{{ $meta['title'] }}</p>
                            <p class="mt-2 text-sm leading-8 text-[var(--color-ink-700)]">{{ $meta['description'] }}</p>
                        </div>
                        <span class="status-pill bg-[var(--color-brand-50)] text-[var(--color-brand-700)]">{{ $groupedPackages[$groupKey]->count() }} عرض</span>
                    </div>

                    <div class="mt-6 grid gap-4 xl:grid-cols-2">
                        @foreach ($groupedPackages[$groupKey] as $row)
                            @php($package = $row['package'])
                            @php($eligibility = $row['eligibility'])
                            @php($inCart = $row['in_cart'])
                            @php($alreadyOwned = $eligibility['state'] === 'already_owned')

                            <article class="surface-card rounded-[2rem] p-5">
                                <div class="flex flex-col gap-5 lg:flex-row">
                                    <div class="catalog-thumb max-w-[12rem] shrink-0 lg:w-[12rem]">
                                        @if ($package->product?->thumbnail_url)
                                            <img src="{{ $package->product->thumbnail_url }}" alt="{{ $package->product?->name_ar }}" loading="lazy" decoding="async">
                                        @else
                                            <div class="catalog-thumb__fallback">
                                                <span>{{ $package->billing_cycle_label ?: 'باقة' }}</span>
                                                <strong>{{ $package->lecture_count }} محاضرة</strong>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="min-w-0 flex-1">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <x-admin.status-badge :label="$package->billing_cycle_label ?: 'باقة رقمية'" />
                                            <x-admin.status-badge :label="$alreadyOwned ? 'تم شراء العرض بالفعل' : ($eligibility['eligible'] ? 'قابلة للشراء' : 'شراء غير متاح')" :tone="$alreadyOwned || $eligibility['eligible'] ? 'success' : 'warning'" />
                                            @if ($inCart && ! $alreadyOwned)
                                                <x-admin.status-badge label="موجودة في السلة" />
                                            @endif
                                        </div>

                                        <h2 class="mt-4 text-xl font-bold">{{ $package->product?->name_ar }}</h2>
                                        <p class="mt-3 text-sm leading-8 text-[var(--color-ink-700)]">{{ $package->product?->description }}</p>

                                        <div class="mt-5 grid gap-3 sm:grid-cols-3">
                                            <div class="stat-tile">
                                                <p class="text-xs text-[var(--color-ink-500)]">السعر</p>
                                                <p class="mt-2 font-semibold">{{ number_format($package->product?->price_amount ?? 0) }} {{ $package->product?->currency }}</p>
                                            </div>
                                            <div class="stat-tile">
                                                <p class="text-xs text-[var(--color-ink-500)]">عدد المحاضرات</p>
                                                <p class="mt-2 font-semibold">{{ $package->items->count() }}</p>
                                            </div>
                                            <div class="stat-tile">
                                                <p class="text-xs text-[var(--color-ink-500)]">مدة التفعيل</p>
                                                <p class="mt-2 font-semibold">{{ $package->access_period_days ? $package->access_period_days.' يوم' : 'حسب الباقة' }}</p>
                                            </div>
                                        </div>

                                        <p class="mt-5 text-sm leading-8 text-[var(--color-ink-700)]">{{ $eligibility['message'] }}</p>
                                        @if ($eligibility['overlaps'] !== [])
                                            <div class="mt-3 flex flex-wrap gap-2">
                                                @foreach ($eligibility['overlaps'] as $title)
                                                    <span class="status-pill status-pill--danger">{{ $title }}</span>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <div class="mt-6 flex flex-wrap gap-3">
                                    <a href="{{ route('student.packages.show', $package) }}" class="btn-primary">عرض التفاصيل</a>

                                    @if ($alreadyOwned)
                                        <button type="button" class="btn-secondary !cursor-default !opacity-70">تم شراء العرض بالفعل</button>
                                    @elseif ($eligibility['eligible'] && $package->product && ! $inCart)
                                        <form method="POST" action="{{ route('student.cart.store') }}">
                                            @csrf
                                            <input type="hidden" name="product_id" value="{{ $package->product->id }}">
                                            <button class="btn-secondary">أضف إلى السلة</button>
                                        </form>
                                    @elseif ($inCart)
                                        <a href="{{ route('student.cart.index') }}" class="btn-secondary">مراجعة السلة</a>
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endforeach

            <div class="px-2">
                {{ $packages->links() }}
            </div>
        @endif
    </section>
</x-layouts.student>
