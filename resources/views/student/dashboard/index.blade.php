<x-layouts.student title="الرئيسية" heading="الرئيسية" subheading="واجهة يومية سريعة لمتابعة المحاضرات والباقات والكتب والتنبيهات والنتائج من شاشة واحدة.">
    <section class="space-y-6">
        <section class="grid gap-6 xl:grid-cols-[1.35fr_0.65fr]">
            <article class="panel-tight overflow-hidden">
                <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
                    <div>
                        <p class="text-sm font-semibold text-[var(--color-brand-700)]">أهلاً بك داخل البوابة</p>
                        <h2 class="mt-2 text-2xl font-bold lg:text-3xl">الرئيسية الآن تعمل كواجهة الطالب الأساسية.</h2>
                        <p class="mt-3 max-w-3xl text-sm leading-8 text-[var(--color-ink-700)]">
                            اختر من الباقات، راجع العناصر المفعلة، وانتقل إلى الكتب أو نتائج الاختبارات بنفس ترتيب التجربة المرجعية لكن داخل الهيكل الحالي للمشروع.
                        </p>
                    </div>
                    <a href="{{ $primaryAction['href'] }}" class="btn-primary">{{ $primaryAction['label'] }}</a>
                </div>

                <div class="mt-6 flex snap-x gap-4 overflow-x-auto pb-2">
                    @foreach ($heroSlides as $slide)
                        <article class="portal-hero-slide min-w-[calc(100%-0.5rem)] snap-center lg:min-w-[34rem]">
                            <div class="max-w-2xl">
                                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-white/70">{{ $slide['eyebrow'] }}</p>
                                <h3 class="mt-4 text-2xl font-bold leading-tight text-white lg:text-3xl">{{ $slide['title'] }}</h3>
                                <p class="mt-4 text-sm leading-8 text-white/80">{{ $slide['description'] }}</p>
                            </div>

                            <div class="mt-8 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                                <div class="rounded-[1.6rem] bg-white/10 px-4 py-4">
                                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-white/60">{{ $slide['metric'] }}</p>
                                    <p class="mt-2 text-2xl font-bold text-white">{{ $slide['value'] }}</p>
                                </div>

                                <div class="flex flex-wrap gap-3">
                                    <a href="{{ $slide['primary_href'] }}" class="btn-primary !bg-white !text-[var(--color-brand-700)]">{{ $slide['primary_label'] }}</a>
                                    <a href="{{ $slide['secondary_href'] }}" class="btn-secondary !border-white/20 !bg-white/10 !text-white">{{ $slide['secondary_label'] }}</a>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </article>

            <aside class="panel-tight">
                <p class="text-sm font-semibold text-[var(--color-brand-700)]">الإجراء الرئيسي</p>
                <h2 class="mt-3 text-2xl font-bold">{{ $primaryAction['title'] }}</h2>
                <p class="mt-4 text-sm leading-8 text-[var(--color-ink-700)]">{{ $primaryAction['description'] }}</p>
                <a href="{{ $primaryAction['href'] }}" class="btn-primary mt-6 w-full">{{ $primaryAction['label'] }}</a>

                <div class="mt-8 rounded-[1.8rem] bg-[var(--color-panel-muted)] p-4">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="text-sm font-semibold text-[var(--color-brand-700)]">آخر العناصر المفعلة</p>
                            <p class="mt-2 text-xs text-[var(--color-ink-500)]">تظهر هنا آخر عناصر الوصول النشطة على الحساب.</p>
                        </div>
                        <a href="{{ route('student.payments.index') }}" class="text-sm font-semibold text-[var(--color-brand-700)]">السجل</a>
                    </div>

                    <div class="mt-4 space-y-3">
                        @forelse ($latestAccessibleContent as $entitlement)
                            <article class="rounded-[1.4rem] bg-white px-4 py-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="font-semibold">{{ $entitlement->item_name_snapshot }}</p>
                                        <p class="mt-2 text-xs text-[var(--color-ink-500)]">{{ optional($entitlement->granted_at)->format('Y/m/d') }}</p>
                                    </div>
                                    <span class="status-pill bg-[var(--color-brand-50)] text-[var(--color-brand-700)]">{{ number_format($entitlement->price_amount) }} ج</span>
                                </div>
                            </article>
                        @empty
                            <x-student.empty-state title="لا يوجد محتوى مفعّل بعد" description="عند تفعيل أي باقة أو وصول رقمي سيظهر هنا تلقائيًا." />
                        @endforelse
                    </div>
                </div>
            </aside>
        </section>

        @if ($notices !== [])
            <section class="grid gap-4 lg:grid-cols-2">
                @foreach ($notices as $notice)
                    <x-student.notice :title="$notice['title']" :body="$notice['body']" :tone="$notice['tone']" />
                @endforeach
            </section>
        @endif

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            @foreach ($stats as $stat)
                <x-student.summary-card :label="$stat['label']" :value="$stat['value']" :description="$stat['description']" />
            @endforeach
        </section>

        @foreach ($packageGroups as $group)
            <section class="panel-tight">
                <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
                    <div>
                        <p class="text-sm font-semibold text-[var(--color-brand-700)]">{{ $group['title'] }}</p>
                        <p class="mt-2 text-sm leading-8 text-[var(--color-ink-700)]">{{ $group['description'] }}</p>
                    </div>
                    <a href="{{ route('student.packages.index') }}" class="btn-secondary">كل الباقات</a>
                </div>

                <div class="mt-6 grid gap-4 xl:grid-cols-3">
                    @foreach ($group['packages'] as $package)
                        <article class="surface-card rounded-[2rem] p-5">
                            <div class="catalog-thumb">
                                @if ($package->product?->thumbnail_url)
                                    <img src="{{ $package->product->thumbnail_url }}" alt="{{ $package->product?->name_ar }}">
                                @else
                                    <div class="catalog-thumb__fallback">
                                        <span>{{ $package->billing_cycle_label ?: 'باقة' }}</span>
                                        <strong>{{ $package->lecture_count }} محاضرة</strong>
                                    </div>
                                @endif
                            </div>

                            <div class="mt-5 flex flex-wrap items-center gap-2">
                                <x-admin.status-badge :label="$package->billing_cycle_label ?: 'باقة رقمية'" />
                                @if ($package->access_period_days)
                                    <x-admin.status-badge :label="$package->access_period_days.' يوم'" tone="warning" />
                                @endif
                            </div>

                            <h3 class="mt-4 text-xl font-bold">{{ $package->product?->name_ar }}</h3>
                            <p class="mt-3 text-sm leading-8 text-[var(--color-ink-700)]">{{ $package->product?->teaser }}</p>

                            <div class="mt-5 flex items-center justify-between gap-3">
                                <span class="text-sm text-[var(--color-ink-500)]">{{ $package->items->count() }} عنصر</span>
                                <span class="text-xl font-bold text-[var(--color-brand-700)]">{{ number_format($package->product?->price_amount ?? 0) }} ج</span>
                            </div>

                            <a href="{{ route('student.packages.show', $package) }}" class="btn-primary mt-5 w-full">عرض التفاصيل</a>
                        </article>
                    @endforeach
                </div>
            </section>
        @endforeach

        <section class="panel-tight">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="text-sm font-semibold text-[var(--color-brand-700)]">الاقسام</p>
                    <p class="mt-2 text-sm leading-8 text-[var(--color-ink-700)]">اختصارات سريعة لأهم أقسام رحلة الطالب داخل المنصة الحالية.</p>
                </div>
                <a href="{{ route('student.lectures.index') }}" class="btn-secondary">الكتالوج الكامل</a>
            </div>

            <div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($sectionCards as $section)
                    @php($accentClasses = match($section['accent']) {
                        'violet' => 'bg-[color-mix(in_oklch,var(--color-violet-100)_64%,white)]',
                        'dark' => 'bg-[color-mix(in_oklch,var(--color-ink-900)_8%,white)]',
                        'brand' => 'bg-[var(--color-brand-50)]',
                        default => 'bg-[color-mix(in_oklch,var(--color-brand-100)_50%,white)]',
                    })
                    <a href="{{ $section['href'] }}" class="portal-section-card {{ $accentClasses }}">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-ink-500)]">{{ $section['count'] }}</p>
                                <h3 class="mt-3 text-xl font-bold">{{ $section['title'] }}</h3>
                            </div>
                            <span class="text-lg text-[var(--color-brand-700)]">↗</span>
                        </div>
                        <p class="mt-4 text-sm leading-8 text-[var(--color-ink-700)]">{{ $section['description'] }}</p>
                    </a>
                @endforeach
            </div>
        </section>

        <section class="panel-tight">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="text-sm font-semibold text-[var(--color-brand-700)]">كتب</p>
                    <p class="mt-2 text-sm leading-8 text-[var(--color-ink-700)]">إصدارات مختارة يمكن إضافتها إلى السلة مباشرة من المتجر الحالي.</p>
                </div>
                <a href="{{ route('student.books.index') }}" class="btn-secondary">كل الكتب</a>
            </div>

            <div class="mt-6 grid gap-4 xl:grid-cols-4">
                @forelse ($featuredBooks as $book)
                    <article class="surface-card rounded-[2rem] p-5">
                        <div class="catalog-thumb catalog-thumb--book">
                            @if ($book->product?->thumbnail_url)
                                <img src="{{ $book->product->thumbnail_url }}" alt="{{ $book->product?->name_ar }}">
                            @else
                                <div class="catalog-thumb__fallback">
                                    <span>{{ $book->cover_badge ?: 'كتاب' }}</span>
                                    <strong>{{ $book->page_count ?: '—' }} صفحة</strong>
                                </div>
                            @endif
                        </div>

                        <div class="mt-5 flex flex-wrap items-center gap-2">
                            <x-admin.status-badge :label="$book->availability_status->label()" :tone="$book->availability_status->tone()" />
                            @if ($book->cover_badge)
                                <x-admin.status-badge :label="$book->cover_badge" />
                            @endif
                        </div>

                        <h3 class="mt-4 text-lg font-bold">{{ $book->product?->name_ar }}</h3>
                        <p class="mt-3 text-sm leading-8 text-[var(--color-ink-700)]">{{ $book->product?->teaser }}</p>

                        <div class="mt-5 flex items-center justify-between gap-3">
                            <span class="text-sm text-[var(--color-ink-500)]">{{ $book->author_name ?: 'إصدار تعليمي' }}</span>
                            <span class="text-xl font-bold text-[var(--color-brand-700)]">{{ number_format($book->product?->price_amount ?? 0) }} ج</span>
                        </div>

                        <a href="{{ route('student.books.show', $book) }}" class="btn-primary mt-5 w-full">عرض الكتاب</a>
                    </article>
                @empty
                    <x-student.empty-state title="لا توجد كتب منشورة حاليًا" description="سيظهر هذا القسم تلقائيًا بمجرد إضافة كتب مفعلة من لوحة الإدارة." />
                @endforelse
            </div>
        </section>

        @if (filled(data_get($featuredVideo, 'embed_url')))
            <section class="grid gap-6 xl:grid-cols-[1.05fr_0.95fr]">
                <article class="panel-tight">
                    <p class="text-sm font-semibold text-[var(--color-brand-700)]">الفيديو</p>
                    <h2 class="mt-2 text-2xl font-bold">{{ data_get($featuredVideo, 'title') }}</h2>
                    <p class="mt-3 text-sm leading-8 text-[var(--color-ink-700)]">{{ data_get($featuredVideo, 'description') }}</p>

                    <div class="mt-6 overflow-hidden rounded-[2rem] border border-[var(--color-border-soft)]">
                        <iframe
                            class="aspect-video w-full"
                            src="{{ data_get($featuredVideo, 'embed_url') }}"
                            title="{{ data_get($featuredVideo, 'title') }}"
                            allowfullscreen
                            loading="lazy"
                        ></iframe>
                    </div>
                </article>

                <aside class="portal-footer-card">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-white/70">الدعم والروابط</p>
                    <h2 class="mt-4 text-3xl font-bold leading-tight text-white">{{ $platformBrand['name'] }}</h2>
                    <p class="mt-4 text-sm leading-8 text-white/80">{{ $platformBrand['tagline'] }}</p>

                    <div class="mt-8 grid gap-6 lg:grid-cols-2">
                        <div>
                            <p class="text-sm font-semibold text-white/80">روابط سريعة</p>
                            <div class="mt-3 flex flex-col gap-2">
                                @foreach ($footerLinks as $link)
                                    <a href="{{ $link['href'] }}" class="portal-footer-link">{{ $link['label'] }}</a>
                                @endforeach
                            </div>
                        </div>

                        <div>
                            <p class="text-sm font-semibold text-white/80">روابط اجتماعية</p>
                            <div class="mt-3 flex flex-col gap-2">
                                @foreach ($socialLinks as $link)
                                    <a href="{{ $link['url'] }}" target="_blank" rel="noreferrer" class="portal-footer-link">{{ $link['label'] }}</a>
                                @endforeach
                                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $platformBrand['support_whatsapp']) }}" target="_blank" rel="noreferrer" class="portal-footer-link">واتساب الدعم</a>
                                <a href="tel:{{ $platformBrand['support_phone'] }}" class="portal-footer-link">اتصال سريع</a>
                            </div>
                        </div>
                    </div>
                </aside>
            </section>
        @endif
    </section>
</x-layouts.student>
