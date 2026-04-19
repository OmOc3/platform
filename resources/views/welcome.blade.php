<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $platformBrand['name'] }} - انطلاقة المنصة</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="surface-shell">
    <main class="mx-auto flex min-h-screen max-w-7xl flex-col justify-center px-6 py-10 lg:px-10">
        <section class="grid gap-8 lg:grid-cols-[1.1fr_0.9fr] lg:items-center">
            <div class="space-y-7">
                <span class="inline-flex rounded-full bg-[var(--color-brand-100)] px-4 py-2 text-sm font-semibold text-[var(--color-brand-700)]">
                    Laravel 12 • Arabic-first • Milestones 0-1
                </span>
                <div class="space-y-4">
                    <p class="font-display text-3xl text-[var(--color-brand-700)] lg:text-4xl">منصة الإتقان التعليمية</p>
                    <h1 class="max-w-3xl text-4xl font-bold leading-tight lg:text-6xl">
                        بنية تشغيل تعليمية عربية لمنتج أكاديمي أكبر من مجرد موقع كورسات.
                    </h1>
                    <p class="max-w-2xl text-lg leading-8 text-[var(--color-ink-700)]">
                        تم تجهيز الأساس المعماري، حراس الدخول، هيكل الوحدات، لوحة الإدارة الأولية، ونواة الصفوف والمسارات والإعدادات وسجل المراجعة.
                    </p>
                </div>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('admin.login') }}" class="btn-primary">دخول الإدارة</a>
                    <a href="{{ route('student.preview') }}" class="btn-secondary">معاينة واجهة الطالب</a>
                </div>
            </div>

            <div class="panel p-6 lg:p-8">
                <div class="grid gap-4 sm:grid-cols-2">
                    <article class="rounded-3xl bg-[var(--color-brand-50)] p-5">
                        <p class="text-sm font-semibold text-[var(--color-brand-700)]">المكتمل الآن</p>
                        <p class="mt-3 text-2xl font-bold">الهوية + الأكاديمي</p>
                        <p class="mt-2 text-sm leading-7 text-[var(--color-ink-700)]">إدارة المشرفين، الصلاحيات، الإعدادات، الصفوف، المسارات، وسجل العمليات الحساسة.</p>
                    </article>
                    <article class="rounded-3xl bg-[color-mix(in_oklch,var(--color-brand-100)_70%,white)] p-5">
                        <p class="text-sm font-semibold text-[var(--color-brand-700)]">الشكل العام</p>
                        <p class="mt-3 text-2xl font-bold">RTL أولاً</p>
                        <p class="mt-2 text-sm leading-7 text-[var(--color-ink-700)]">تصميم دافئ وعملي للإدارة والطالب مع مساحات واضحة ونظام ألوان قابل للتوسع.</p>
                    </article>
                    <article class="rounded-3xl bg-white p-5 ring-1 ring-[color-mix(in_oklch,var(--color-brand-100)_75%,white)]">
                        <p class="text-sm font-semibold text-[var(--color-brand-700)]">الوثائق</p>
                        <p class="mt-3 text-2xl font-bold">architecture.md</p>
                        <p class="mt-2 text-sm leading-7 text-[var(--color-ink-700)]">توثيق الحدود، العقود، الحراس، وأسلوب التطوير لكل وحدة.</p>
                    </article>
                    <article class="rounded-3xl bg-white p-5 ring-1 ring-[color-mix(in_oklch,var(--color-brand-100)_75%,white)]">
                        <p class="text-sm font-semibold text-[var(--color-brand-700)]">المراحل التالية</p>
                        <p class="mt-3 text-2xl font-bold">الطالب + المحتوى</p>
                        <p class="mt-2 text-sm leading-7 text-[var(--color-ink-700)]">سطح الطالب الكامل، إدارة الطلاب، المحتوى، الامتحانات، المتجر، والدعم.</p>
                    </article>
                </div>
            </div>
        </section>
    </main>
</body>
</html>
