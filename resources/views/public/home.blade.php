<x-layouts.public :title="$platformBrand['name']">
    <div>
        <header class="panel overflow-hidden px-6 py-8 lg:px-10 lg:py-12">
            <div class="grid gap-8 lg:grid-cols-[1.08fr_0.92fr] lg:items-start">
                <div>
                    <p class="section-kicker">{{ $publicContent['hero_badge'] }}</p>
                    <p class="mt-4 font-display text-3xl text-[var(--color-brand-700)] lg:text-4xl">{{ $platformBrand['teacher_name'] }}</p>
                    <h1 class="mt-5 max-w-3xl text-4xl font-bold leading-tight lg:text-6xl">{{ $publicContent['hero_title'] }}</h1>
                    <p class="mt-6 max-w-2xl text-base leading-8 text-[var(--color-ink-700)] lg:text-lg">{{ $publicContent['hero_description'] }}</p>

                    <div class="mt-8 flex flex-wrap gap-3">
                        <a href="{{ route('student.register') }}" class="btn-primary">إنشاء حساب طالب</a>
                        <a href="{{ route('student.login') }}" class="btn-secondary">دخول الطالب</a>
                        <a href="{{ route('admin.login') }}" class="btn-secondary">دخول الإدارة</a>
                    </div>

                    <div class="surface-inset mt-8 grid gap-3 rounded-[1.4rem] p-4 md:grid-cols-3">
                        <div>
                            <span class="stat-tile__label">منهجية العمل</span>
                            <strong class="mt-2 block text-base font-bold">شرح + متابعة</strong>
                        </div>
                        <div class="border-y border-[var(--color-border-soft)] py-3 md:border-x md:border-y-0 md:px-4 md:py-0">
                            <span class="stat-tile__label">التجربة</span>
                            <strong class="mt-2 block text-base font-bold">سريعة وواضحة</strong>
                        </div>
                        <div>
                            <span class="stat-tile__label">اللغة</span>
                            <strong class="mt-2 block text-base font-bold">عربية أولًا</strong>
                        </div>
                    </div>
                </div>

                <div class="grid gap-4">
                    <section class="panel-tight">
                        <p class="section-kicker">ملامح التجربة</p>
                        <div class="mt-5 grid gap-3">
                            <div class="surface-inset rounded-[1.3rem] p-4">
                                <p class="text-sm font-semibold text-[var(--color-ink-900)]">منصة واحدة تربط الشرح والباقات والكتب والمتابعة.</p>
                                <p class="mt-2 text-sm leading-7 text-[var(--color-ink-700)]">لا توجد قفزات بين أنظمة منفصلة أو صفحات تسويقية منفصلة عن التشغيل الحقيقي.</p>
                            </div>
                            <div class="surface-inset rounded-[1.3rem] p-4">
                                <p class="text-sm font-semibold text-[var(--color-ink-900)]">بوابة طالب عملية وليست مجرد عرض محتوى.</p>
                                <p class="mt-2 text-sm leading-7 text-[var(--color-ink-700)]">النتائج، الحضور، المدفوعات، والدعم كلها ضمن مسار واحد واضح.</p>
                            </div>
                        </div>
                    </section>

                    <section class="surface-card rounded-[1.5rem] p-6 lg:p-7">
                        <p class="section-kicker">رؤية المنصة</p>
                        <p class="mt-4 text-2xl font-bold leading-snug">هوية أكاديمية هادئة تضع الطالب أمام ما يحتاجه الآن، بلا حشو أو ضوضاء بصرية.</p>
                        <p class="mt-4 text-sm leading-8 text-[var(--color-ink-700)]">الصفحة العامة هنا تقدم الثقة والاتجاه، ثم تنقل الطالب مباشرة إلى تجربة الاستخدام الحقيقية داخل البوابة.</p>
                    </section>
                </div>
            </div>
        </header>

        <section class="mt-14 panel-tight">
            <x-public.section-heading eyebrow="مزايا المنصة" title="أدوات تخدم رحلة الطالب من أول تسجيل حتى المتابعة اليومية." description="كل قسم هنا يؤدي وظيفة واضحة داخل التجربة التعليمية، مع تركيز على الوضوح وسهولة الحركة بين المحتوى والعمليات." />
            <div class="mt-8 grid gap-6 md:grid-cols-2 xl:grid-cols-4">
                @foreach ($publicContent['features'] as $feature)
                    <article class="space-y-3">
                        <p class="section-kicker">تفصيلة تشغيلية</p>
                        <p class="text-lg font-bold text-[var(--color-ink-900)]">{{ $feature['title'] }}</p>
                        <p class="text-sm leading-8 text-[var(--color-ink-700)]">{{ $feature['description'] }}</p>
                    </article>
                @endforeach
            </div>
        </section>

        <section class="mt-16 grid gap-8 lg:grid-cols-[0.82fr_1.18fr] lg:items-start">
            <div class="panel-tight">
                <x-public.section-heading eyebrow="قصص نجاح" title="نتائج واقعية تبني الثقة قبل الاشتراك." description="إبراز التفوق جزء من إثبات جودة الرحلة التعليمية، لا مجرد عنصر تزييني داخل الصفحة." />
            </div>

            <div class="grid gap-4 md:grid-cols-3">
                @foreach ($publicContent['achievers'] as $achiever)
                    <article class="surface-card-soft rounded-[1.5rem] p-5">
                        <p class="section-kicker">نتيجة</p>
                        <p class="mt-3 text-3xl font-bold text-[var(--color-brand-700)]">{{ $achiever['score'] }}</p>
                        <p class="mt-5 text-sm font-semibold">{{ $achiever['name'] }}</p>
                        <p class="mt-3 text-sm leading-8 text-[var(--color-ink-700)]">{{ $achiever['achievement'] }}</p>
                    </article>
                @endforeach
            </div>
        </section>

        <section class="mt-16">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <x-public.section-heading eyebrow="الباقات" title="أحدث الباقات الرقمية" description="باقات مبنية على مسارات متابعة واضحة، مع تقديم المحتوى الأكاديمي قبل أي صخب تسويقي." />
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
                <x-public.section-heading eyebrow="الكتب" title="كتب ومذكرات مختارة" description="إصدارات مركزة للمراجعة والتثبيت، بنفس اللغة البصرية الهادئة المستخدمة في بقية المنصة." />
                <a href="{{ route('student.books.index') }}" class="btn-secondary">استعرض بوابة الطالب</a>
            </div>

            <div class="mt-8 grid gap-4 lg:grid-cols-2">
                @foreach ($featuredBooks as $product)
                    <x-public.catalog-card :product="$product" :meta="[$product->book?->cover_badge ?? 'كتاب', ($product->book?->page_count ?? 0).' صفحة']" tone="book" />
                @endforeach
            </div>
        </section>

        <section class="mt-16 grid gap-6 lg:grid-cols-[1fr_0.92fr] lg:items-stretch">
            <div class="panel-tight">
                <x-public.section-heading eyebrow="فيديو" :title="$publicContent['video']['title']" :description="$publicContent['video']['description']" />
                <div class="mt-6 overflow-hidden rounded-[1.6rem] border border-[var(--color-border-soft)]">
                    <iframe class="aspect-video w-full" src="{{ $publicContent['video']['embed_url'] }}" title="{{ $publicContent['video']['title'] }}" allowfullscreen loading="lazy"></iframe>
                </div>
            </div>

            <div class="surface-card rounded-[1.7rem] p-6 lg:p-8">
                <p class="section-kicker">لماذا هذه المنصة؟</p>
                <h2 class="mt-4 text-3xl font-bold leading-tight">التركيز هنا على منتج تعليمي تشغيلي، لا مجرد واجهة لعرض الكورسات.</h2>
                <p class="mt-5 text-sm leading-8 text-[var(--color-ink-700)]">الصفحة العامة تقود إلى الحساب، ثم تترك المجال لبوابة الطالب كي تعرض البيانات الفعلية: الوصول، الحضور، المدفوعات، والدعم.</p>
            </div>
        </section>

        <footer class="mt-16 panel-tight">
            <div class="grid gap-8 lg:grid-cols-[1fr_0.8fr_0.8fr]">
                <div>
                    <p class="font-display text-3xl text-[var(--color-brand-700)]">{{ $platformBrand['name'] }}</p>
                    <p class="mt-4 max-w-xl text-sm leading-8 text-[var(--color-ink-700)]">{{ $platformBrand['tagline'] }}</p>
                </div>

                <div>
                    <p class="section-kicker">روابط سريعة</p>
                    <div class="mt-4 flex flex-col gap-3 text-sm">
                        @foreach ($publicContent['footer_links'] as $link)
                            <a href="{{ route($link['route']) }}" class="text-[var(--color-ink-700)] hover:text-[var(--color-ink-900)]">{{ $link['label'] }}</a>
                        @endforeach
                    </div>
                </div>

                <div>
                    <p class="section-kicker">روابط اجتماعية</p>
                    <div class="mt-4 flex flex-col gap-3 text-sm">
                        @foreach ($publicContent['social_links'] as $link)
                            <a href="{{ $link['url'] }}" target="_blank" rel="noreferrer" class="text-[var(--color-ink-700)] hover:text-[var(--color-ink-900)]">{{ $link['label'] }}</a>
                        @endforeach
                    </div>
                </div>
            </div>
        </footer>
    </div>
</x-layouts.public>
