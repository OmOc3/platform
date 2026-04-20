<x-layouts.public :title="$platformBrand['name']">
    <main class="mx-auto max-w-7xl px-6 py-6 lg:px-10">
        <header class="panel overflow-hidden px-6 py-8 lg:px-10 lg:py-10">
            <div class="grid gap-10 lg:grid-cols-[1.05fr_0.95fr] lg:items-center">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[var(--color-brand-700)]">{{ $publicContent['hero_badge'] }}</p>
                    <p class="mt-4 font-display text-3xl text-[var(--color-brand-700)] lg:text-4xl">{{ $platformBrand['teacher_name'] }}</p>
                    <h1 class="mt-5 max-w-3xl text-4xl font-bold leading-tight lg:text-6xl">{{ $publicContent['hero_title'] }}</h1>
                    <p class="mt-6 max-w-2xl text-base leading-8 text-[var(--color-ink-700)] lg:text-lg">{{ $publicContent['hero_description'] }}</p>

                    <div class="mt-8 flex flex-wrap gap-3">
                        <a href="{{ route('student.register') }}" class="btn-primary">إنشاء حساب طالب</a>
                        <a href="{{ route('student.login') }}" class="btn-secondary">دخول الطالب</a>
                        <a href="{{ route('admin.login') }}" class="btn-secondary">دخول الإدارة</a>
                    </div>
                </div>

                <div class="grid gap-4 lg:grid-cols-2">
                    <article class="rounded-[2.2rem] bg-[var(--color-brand-700)] p-6 text-white">
                        <p class="text-sm font-semibold text-white/80">أسلوب العمل</p>
                        <p class="mt-4 text-2xl font-bold leading-snug">منصة واحدة تربط الباقات، الكتب، المتابعة، والسنتر بدون تشتيت.</p>
                    </article>
                    <article class="surface-card-soft rounded-[2.2rem] p-6">
                        <p class="text-sm font-semibold text-[var(--color-brand-700)]">تجربة الطالب</p>
                        <p class="mt-4 text-xl font-bold">تسجيل، متابعة، وسجل واضح للمدفوعات والحضور والشكاوى.</p>
                    </article>
                    <article class="surface-card rounded-[2.2rem] p-6">
                        <p class="text-sm font-semibold text-[var(--color-brand-700)]">قيمة حقيقية</p>
                        <p class="mt-4 text-sm leading-8 text-[var(--color-ink-700)]">تصميم عربي أولًا يركز على وضوح المسار التعليمي بدل تكديس الصفحات والوظائف بدون ترابط.</p>
                    </article>
                    <article class="surface-card rounded-[2.2rem] p-6">
                        <p class="text-sm font-semibold text-[var(--color-brand-700)]">متابعة منظمة</p>
                        <p class="mt-4 text-sm leading-8 text-[var(--color-ink-700)]">البداية هنا هي الأساس العام، مع قابلية مباشرة للتوسع إلى الامتحانات، المنتدى، وتتبع الأخطاء.</p>
                    </article>
                </div>
            </div>
        </header>

        <section class="mt-14">
            <x-public.section-heading eyebrow="مزايا المنصة" title="أدوات تخدم رحلة الطالب من أول تسجيل حتى المتابعة اليومية." description="كل قسم مصمم ليحل خطوة فعلية داخل تجربة الطالب وولي الأمر، وليس مجرد إضافة شكلية إلى المنصة." />
            <div class="mt-8 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                @foreach ($publicContent['features'] as $feature)
                    <x-public.feature-card :title="$feature['title']" :description="$feature['description']" />
                @endforeach
            </div>
        </section>

        <section class="mt-16 grid gap-8 lg:grid-cols-[0.85fr_1.15fr] lg:items-start">
            <div class="panel-tight">
                <x-public.section-heading eyebrow="قصص نجاح" title="نماذج أداء تمنح ثقة قبل بدء الاشتراك." description="إبراز التفوق هنا جزء من بناء الثقة وليس مجرد أرقام معزولة عن السياق." />
            </div>

            <div class="grid gap-4 md:grid-cols-3">
                @foreach ($publicContent['achievers'] as $achiever)
                    <article class="surface-card rounded-[2rem] p-5">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-ink-500)]">نتيجة</p>
                        <p class="mt-3 text-3xl font-bold text-[var(--color-brand-700)]">{{ $achiever['score'] }}</p>
                        <p class="mt-5 text-sm font-semibold">{{ $achiever['name'] }}</p>
                        <p class="mt-3 text-sm leading-8 text-[var(--color-ink-700)]">{{ $achiever['achievement'] }}</p>
                    </article>
                @endforeach
            </div>
        </section>

        <section class="mt-16">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <x-public.section-heading eyebrow="الباقات" title="أحدث الباقات الرقمية" description="باقات مهيأة لرحلة متابعة منتظمة وتدرج منطقي في الشرح والمراجعة." />
                <a href="{{ route('student.register') }}" class="btn-secondary">ابدأ من هنا</a>
            </div>

            <div class="mt-8 grid gap-4 lg:grid-cols-3">
                @foreach ($featuredPackages as $product)
                    <x-public.catalog-card :product="$product" :meta="[$product->package?->billing_cycle_label ?? 'باقة رقمية', ($product->package?->lecture_count ?? 0).' محاضرة']" />
                @endforeach
            </div>
        </section>

        <section class="mt-16">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <x-public.section-heading eyebrow="الكتب" title="كتب ومذكرات مختارة" description="إصدارات مركزة تخدم المراجعة والتثبيت، مع إبراز بسيط يصلح للتوسع لاحقًا إلى متجر كامل." />
                <a href="{{ route('student.books.index') }}" class="btn-secondary">استعراض بوابة الطالب</a>
            </div>

            <div class="mt-8 grid gap-4 lg:grid-cols-2">
                @foreach ($featuredBooks as $product)
                    <x-public.catalog-card :product="$product" :meta="[$product->book?->cover_badge ?? 'كتاب', ($product->book?->page_count ?? 0).' صفحة']" tone="book" />
                @endforeach
            </div>
        </section>

        <section class="mt-16 grid gap-6 lg:grid-cols-[1fr_0.9fr] lg:items-stretch">
            <div class="panel-tight">
                <x-public.section-heading eyebrow="فيديو" :title="$publicContent['video']['title']" :description="$publicContent['video']['description']" />
                <div class="mt-6 overflow-hidden rounded-[2rem] shadow-[0_20px_50px_rgba(71,58,29,0.12)]">
                    <iframe class="aspect-video w-full" src="{{ $publicContent['video']['embed_url'] }}" title="{{ $publicContent['video']['title'] }}" allowfullscreen></iframe>
                </div>
            </div>

            <div class="panel-tight bg-[var(--color-brand-700)] text-white">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-white/70">لماذا هذه المنصة؟</p>
                <h2 class="mt-4 text-3xl font-bold leading-tight">التركيز هنا على تجربة تعليمية تشغيلية، لا مجرد واجهة لعرض كورسات.</h2>
                <p class="mt-5 text-sm leading-8 text-white/80">الصفحة العامة تقود إلى الحساب، والبوابة تقود إلى البيانات الفعلية: حالة الطالب، سجل المدفوعات، الحضور، وسجل الدعم. هذا الترابط هو ما يجعل المنصة صالحة للنمو لاحقًا.</p>
            </div>
        </section>

        <footer class="mt-16 rounded-[2.5rem] bg-[var(--color-ink-900)] px-6 py-8 text-white lg:px-10">
            <div class="grid gap-8 lg:grid-cols-[1fr_0.8fr_0.8fr]">
                <div>
                    <p class="font-display text-3xl text-[var(--color-brand-100)]">{{ $platformBrand['name'] }}</p>
                    <p class="mt-4 max-w-xl text-sm leading-8 text-white/75">{{ $platformBrand['tagline'] }}</p>
                </div>

                <div>
                    <p class="text-sm font-semibold text-white/80">روابط سريعة</p>
                    <div class="mt-4 flex flex-col gap-3 text-sm">
                        @foreach ($publicContent['footer_links'] as $link)
                            <a href="{{ route($link['route']) }}" class="text-white/80 hover:text-white">{{ $link['label'] }}</a>
                        @endforeach
                    </div>
                </div>

                <div>
                    <p class="text-sm font-semibold text-white/80">روابط اجتماعية</p>
                    <div class="mt-4 flex flex-col gap-3 text-sm">
                        @foreach ($publicContent['social_links'] as $link)
                            <a href="{{ $link['url'] }}" target="_blank" rel="noreferrer" class="text-white/80 hover:text-white">{{ $link['label'] }}</a>
                        @endforeach
                    </div>
                </div>
            </div>
        </footer>
    </main>
</x-layouts.public>
