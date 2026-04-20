<x-layouts.student :title="$package->product?->name_ar" :heading="$package->product?->name_ar" subheading="صفحة تفصيلية للباقة تعرض المحتوى، الاختبارات والواجبات، والملفات المرفقة بنفس منطق الشراء والوصول الحالي داخل المنصة.">
    @php($alreadyOwned = $eligibility['state'] === 'already_owned')
    <section class="grid gap-6 xl:grid-cols-[1.22fr_0.78fr]">
        <article class="space-y-6">
            <section class="panel-tight">
                <div class="grid gap-5 lg:grid-cols-[0.85fr_1.15fr] lg:items-center">
                    <div class="catalog-thumb min-h-[16rem]">
                        @if ($package->product?->thumbnail_url)
                            <img src="{{ $package->product->thumbnail_url }}" alt="{{ $package->product?->name_ar }}">
                        @else
                            <div class="catalog-thumb__fallback">
                                <span>{{ $package->billing_cycle_label ?: 'باقة رقمية' }}</span>
                                <strong>{{ $package->lecture_count }} محاضرة</strong>
                            </div>
                        @endif
                    </div>

                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <x-admin.status-badge :label="$package->billing_cycle_label ?: 'باقة'" />
                            <x-admin.status-badge :label="$alreadyOwned ? 'تم شراء العرض بالفعل' : ($eligibility['eligible'] ? 'قابلة للشراء' : 'شراء غير متاح')" :tone="$alreadyOwned || $eligibility['eligible'] ? 'success' : 'warning'" />
                            @if ($inCart && ! $alreadyOwned)
                                <x-admin.status-badge label="موجودة في السلة" />
                            @endif
                        </div>

                        <p class="mt-5 text-base leading-9 text-[var(--color-ink-700)]">{{ $package->product?->description }}</p>

                        <div class="mt-6 grid gap-3 sm:grid-cols-3">
                            <div class="rounded-[1.4rem] bg-[var(--color-brand-50)] p-4">
                                <p class="text-xs text-[var(--color-ink-500)]">السعر</p>
                                <p class="mt-2 font-semibold">{{ number_format($package->product?->price_amount ?? 0) }} {{ $package->product?->currency }}</p>
                            </div>
                            <div class="rounded-[1.4rem] bg-[var(--color-brand-50)] p-4">
                                <p class="text-xs text-[var(--color-ink-500)]">عدد المحاضرات</p>
                                <p class="mt-2 font-semibold">{{ $contentItems->count() }}</p>
                            </div>
                            <div class="rounded-[1.4rem] bg-[var(--color-brand-50)] p-4">
                                <p class="text-xs text-[var(--color-ink-500)]">مدة التفعيل</p>
                                <p class="mt-2 font-semibold">{{ $package->access_period_days ? $package->access_period_days.' يوم' : 'حسب الباقة' }}</p>
                            </div>
                        </div>

                        <div class="mt-6 flex flex-wrap gap-3">
                            <a href="#package-content" class="btn-secondary">محتوى</a>
                            <a href="#package-questions" class="btn-secondary">الأسئلة والواجبات</a>
                            <a href="#package-files" class="btn-secondary">الملفات</a>
                        </div>
                    </div>
                </div>
            </section>

            <section id="package-content" class="panel-tight">
                <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
                    <div>
                        <p class="text-sm font-semibold text-[var(--color-brand-700)]">محتوى</p>
                        <p class="mt-2 text-sm leading-8 text-[var(--color-ink-700)]">المحاضرات المدرجة داخل الباقة مع حالة كل عنصر بالنسبة لحسابك الآن.</p>
                    </div>
                    <span class="status-pill bg-[var(--color-brand-50)] text-[var(--color-brand-700)]">{{ $contentItems->count() }} عنصر</span>
                </div>

                <div class="mt-6 space-y-4">
                    @foreach ($contentItems as $row)
                        @php($lecture = $row['lecture'])
                        @php($access = $row['access'])
                        @php($isOpen = in_array($access['state']->value, ['open', 'free', 'owned_via_entitlement'], true))

                        <article class="surface-card rounded-[1.8rem] p-5">
                            <div class="flex flex-col gap-5 lg:flex-row">
                                <div class="catalog-thumb max-w-[10rem] shrink-0 lg:w-[10rem]">
                                    @if ($lecture->thumbnail_url)
                                        <img src="{{ $lecture->thumbnail_url }}" alt="{{ $lecture->title }}">
                                    @else
                                        <div class="catalog-thumb__fallback">
                                            <span>{{ $lecture->lectureSection?->name_ar ?? 'محاضرة' }}</span>
                                            <strong>{{ $lecture->duration_minutes ? $lecture->duration_minutes.' دقيقة' : 'بدون مدة' }}</strong>
                                        </div>
                                    @endif
                                </div>

                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <x-student.access-state :access="$access" />
                                        <x-admin.status-badge :label="$isOpen ? 'فتح المحاضرة' : 'متاحة بعد التفعيل'" :tone="$isOpen ? 'success' : 'warning'" />
                                        @if ($row['deadline'])
                                            <span class="status-pill bg-[color-mix(in_oklch,var(--color-danger)_12%,white)] text-[color-mix(in_oklch,var(--color-danger)_75%,black)]">{{ $row['deadline'] }}</span>
                                        @endif
                                    </div>

                                    <h3 class="mt-4 text-lg font-bold">{{ $lecture->title }}</h3>
                                    <p class="mt-3 text-sm leading-8 text-[var(--color-ink-700)]">{{ $lecture->short_description }}</p>

                                    <div class="mt-4 flex flex-wrap gap-3 text-sm text-[var(--color-ink-700)]">
                                        <span>{{ $lecture->lectureSection?->name_ar ?? 'قسم عام' }}</span>
                                        <span>{{ $lecture->duration_minutes ? $lecture->duration_minutes.' دقيقة' : 'بدون مدة زمنية' }}</span>
                                        <span>{{ $row['files']->count() }} ملف</span>
                                        <span>{{ $row['related_exams']->count() }} اختبار</span>
                                    </div>

                                    <div class="mt-5 flex flex-wrap gap-3">
                                        @if ($isOpen)
                                            <a href="{{ route('student.lectures.show', $lecture) }}" class="btn-primary">فتح المحاضرة</a>
                                        @else
                                            <span class="btn-secondary !cursor-default !opacity-70">متاحة بعد شراء الباقة</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>

            <section id="package-questions" class="panel-tight">
                <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
                    <div>
                        <p class="text-sm font-semibold text-[var(--color-brand-700)]">الأسئلة والواجبات</p>
                        <p class="mt-2 text-sm leading-8 text-[var(--color-ink-700)]">اختبارات مرتبطة بالمحاضرات الموجودة داخل الباقة، مع عدد المحاولات المتبقية لكل اختبار.</p>
                    </div>
                    <span class="status-pill bg-[var(--color-brand-50)] text-[var(--color-brand-700)]">{{ $questionItems->count() }} اختبار</span>
                </div>

                @if ($questionItems->isEmpty())
                    <div class="mt-6">
                        <x-student.empty-state title="لا توجد اختبارات مرتبطة بعد" description="سيظهر هنا أي اختبار أو واجب مرتبط بمحاضرات الباقة عند نشره من لوحة الإدارة." />
                    </div>
                @else
                    <div class="mt-6 grid gap-4 xl:grid-cols-2">
                        @foreach ($questionItems as $row)
                            <article class="surface-card rounded-[1.8rem] p-5">
                                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-ink-500)]">{{ $row['lecture']->title }}</p>
                                <h3 class="mt-3 text-lg font-bold">{{ $row['exam']->title }}</h3>
                                <p class="mt-3 text-sm leading-8 text-[var(--color-ink-700)]">{{ $row['exam']->short_description }}</p>

                                <div class="mt-4 grid gap-3 sm:grid-cols-2">
                                    <div class="rounded-[1.4rem] bg-[var(--color-brand-50)] p-4">
                                        <p class="text-xs text-[var(--color-ink-500)]">عدد المحاولات</p>
                                        <p class="mt-2 font-semibold">لديك {{ $row['remaining_attempts'] }} محاولات لهذا الاختبار</p>
                                    </div>
                                    <div class="rounded-[1.4rem] bg-[var(--color-brand-50)] p-4">
                                        <p class="text-xs text-[var(--color-ink-500)]">المدة</p>
                                        <p class="mt-2 font-semibold">{{ $row['exam']->duration_minutes ? $row['exam']->duration_minutes.' دقيقة' : 'بدون حد زمني' }}</p>
                                    </div>
                                </div>

                                <div class="mt-5 flex flex-wrap gap-3">
                                    <a href="{{ $row['cta']['href'] }}" class="btn-primary">{{ $row['cta']['label'] }}</a>
                                    @if ($isCampOffer)
                                        <a href="{{ route('student.complaints.index') }}" class="btn-secondary">طلب مد الوقت 12 ساعة</a>
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </div>
                @endif
            </section>

            <section id="package-files" class="panel-tight">
                <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
                    <div>
                        <p class="text-sm font-semibold text-[var(--color-brand-700)]">الملفات</p>
                        <p class="mt-2 text-sm leading-8 text-[var(--color-ink-700)]">ملفات وروابط مساندة تم نشرها مع المحاضرات الموجودة داخل الباقة.</p>
                    </div>
                    <span class="status-pill bg-[var(--color-brand-50)] text-[var(--color-brand-700)]">{{ $fileItems->count() }} ملف</span>
                </div>

                @if ($fileItems->isEmpty())
                    <div class="mt-6">
                        <x-student.empty-state title="لا توجد ملفات مرفقة بعد" description="عند إضافة ملفات أو روابط تعليمية للمحاضرات المدرجة في هذه الباقة ستظهر هنا مباشرة." />
                    </div>
                @else
                    <div class="mt-6 grid gap-4 xl:grid-cols-2">
                        @foreach ($fileItems as $row)
                            <article class="surface-card rounded-[1.8rem] p-5">
                                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-ink-500)]">{{ $row['lecture']->title }}</p>
                                <h3 class="mt-3 text-lg font-bold">{{ $row['asset']->title }}</h3>
                                @if ($row['asset']->body)
                                    <p class="mt-3 text-sm leading-8 text-[var(--color-ink-700)]">{{ $row['asset']->body }}</p>
                                @endif
                                <a href="{{ $row['asset']->url }}" target="_blank" rel="noreferrer" class="btn-primary mt-5">فتح الملف</a>
                            </article>
                        @endforeach
                    </div>
                @endif
            </section>
        </article>

        <aside class="space-y-6">
            <section class="panel-tight">
                <p class="text-sm font-semibold text-[var(--color-brand-700)]">قرار الشراء</p>
                <h2 class="mt-3 text-2xl font-bold">{{ $package->product?->name_ar }}</h2>
                <p class="mt-4 text-sm leading-8 text-[var(--color-ink-700)]">{{ $eligibility['message'] }}</p>

                @if ($eligibility['overlaps'] !== [])
                    <div class="mt-4 flex flex-wrap gap-2">
                        @foreach ($eligibility['overlaps'] as $title)
                            <span class="status-pill bg-[color-mix(in_oklch,var(--color-danger)_10%,white)] text-[color-mix(in_oklch,var(--color-danger)_70%,black)]">{{ $title }}</span>
                        @endforeach
                    </div>
                @endif

                <div class="mt-6 flex flex-col gap-3">
                    @if ($alreadyOwned)
                        <button type="button" class="btn-secondary !cursor-default !opacity-70">تم شراء العرض بالفعل</button>
                    @elseif ($eligibility['eligible'] && $package->product && ! $inCart)
                        <form method="POST" action="{{ route('student.cart.store') }}">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $package->product->id }}">
                            <button class="btn-primary w-full">أضف الباقة إلى السلة</button>
                        </form>
                    @elseif ($inCart)
                        <a href="{{ route('student.cart.index') }}" class="btn-primary w-full">الباقة موجودة في السلة</a>
                    @else
                        <button type="button" class="btn-secondary !cursor-default !opacity-70">الشراء غير متاح حاليًا</button>
                    @endif

                    <a href="{{ route('student.packages.index') }}" class="btn-secondary">العودة إلى الباقات</a>
                </div>
            </section>

            @if ($isCampOffer)
                <section class="panel-tight">
                    <p class="text-sm font-semibold text-[var(--color-brand-700)]">تنبيهات المعسكر</p>
                    <div class="mt-4 rounded-[1.6rem] bg-[color-mix(in_oklch,var(--color-violet-100)_64%,white)] p-4 text-[var(--color-violet-700)]">
                        <p class="font-semibold">وضع مراجعة مكثف</p>
                        <p class="mt-2 text-sm leading-8">هذه الباقة تحمل نمط معسكر، لذلك أضفنا اختصارًا مباشرًا لطلب مد الوقت 12 ساعة عند الحاجة عبر بوابة الدعم.</p>
                    </div>
                    <a href="{{ route('student.complaints.index') }}" class="btn-secondary mt-5 w-full">طلب مد الوقت 12 ساعة</a>
                </section>
            @endif

            <section class="panel-tight">
                <div class="flex items-center justify-between gap-3">
                    <p class="text-sm font-semibold text-[var(--color-brand-700)]">محاضرات اخرى</p>
                    <a href="{{ route('student.lectures.index') }}" class="text-sm font-semibold text-[var(--color-brand-700)]">الكتالوج</a>
                </div>

                <div class="mt-4 space-y-3">
                    @forelse ($recommendations as $row)
                        <article class="rounded-[1.6rem] bg-[var(--color-panel-muted)] px-4 py-4">
                            <div class="flex flex-wrap items-center gap-2">
                                <x-student.access-state :access="$row['access']" />
                            </div>
                            <h3 class="mt-3 font-semibold">{{ $row['lecture']->title }}</h3>
                            <p class="mt-2 text-sm leading-7 text-[var(--color-ink-700)]">{{ $row['lecture']->short_description }}</p>
                            <a href="{{ route('student.lectures.show', $row['lecture']) }}" class="btn-secondary mt-4 w-full !py-2">عرض المحاضرة</a>
                        </article>
                    @empty
                        <x-student.empty-state title="لا توجد توصيات إضافية" description="كل المحاضرات النشطة المطابقة لصفك مدرجة بالفعل داخل هذه الباقة." />
                    @endforelse
                </div>
            </section>
        </aside>
    </section>
</x-layouts.student>
