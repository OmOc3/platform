<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ ($title ?? 'بوابة الطالب').' - '.$platformBrand['name'] }}</title>
    <x-font-links />
    <x-theme.init-script />
    @livewireStyles
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="surface-shell">
    <a href="#student-main" class="skip-link">تخطَّ إلى المحتوى</a>
    @php($student = auth('student')->user())
    @php($items = app(\App\Shared\Support\Navigation\StudentNavigation::class)->items())
    @php($cartCount = $student?->cart()->withCount('items')->first()?->items_count ?? 0)
    @php($moreLinks = [
        ['label' => 'الملف الشخصي', 'href' => route('student.profile.show')],
        ['label' => 'مدفوعات المحاضرات', 'href' => route('student.payments.index')],
        ['label' => 'طلبات الكتب', 'href' => route('student.book-orders.index')],
        ['label' => 'حضور السنتر', 'href' => route('student.attendance.index')],
        ['label' => 'الشكاوى والاقتراحات', 'href' => route('student.complaints.index')],
    ])
    @php($navIcons = [
        'الرئيسية' => '⌂',
        'المحاضرات' => '▶',
        'الباقات' => '▤',
        'كتب' => '▦',
        'ملتقى الأسئلة' => '؟',
        'أخطائي' => '!',
    ])
    @php($statusLabel = $student?->status?->label() ?? '—')
    @php($teacherParts = collect(preg_split('/\s+/u', trim($platformBrand['teacher_name'])))->filter()->values())
    @php($teacherInitials = $teacherParts->take(2)->map(fn ($part) => mb_substr($part, 0, 1))->join(''))

    <div class="mx-auto max-w-[96rem] px-4 pb-12 pt-4 lg:px-6">
        <header class="panel mb-6 overflow-hidden">
            <div class="px-4 py-4 lg:px-6 lg:py-6">
                <div class="flex flex-col gap-6">
                    <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                        <div class="flex items-center gap-4">
                            <div class="portal-avatar">{{ $teacherInitials !== '' ? $teacherInitials : 'أ' }}</div>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-ink-500)]">المعلم</p>
                                <p class="font-display text-2xl text-[var(--color-brand-700)] lg:text-3xl">{{ $platformBrand['teacher_name'] }}</p>
                                <p class="mt-1 text-sm text-[var(--color-ink-700)]">{{ $platformBrand['name'] }}</p>
                            </div>
                        </div>

                        <div class="grid gap-3 sm:grid-cols-2 xl:w-full xl:max-w-[28rem] xl:grid-cols-3">
                            <div class="portal-shell-meta">
                                <span class="portal-shell-meta__label">الطالب</span>
                                <strong class="portal-shell-meta__value">{{ $student?->name }}</strong>
                            </div>
                            <div class="portal-shell-meta">
                                <span class="portal-shell-meta__label">الكود</span>
                                <strong class="portal-shell-meta__value">{{ $student?->student_number ?: '—' }}</strong>
                            </div>
                            <div class="portal-shell-meta">
                                <span class="portal-shell-meta__label">الحالة</span>
                                <strong class="portal-shell-meta__value">{{ $statusLabel }}</strong>
                            </div>
                        </div>
                    </div>

                    <div class="surface-inset grid gap-4 rounded-[1.5rem] p-4 lg:grid-cols-[1.1fr_0.9fr] lg:items-center">
                        <div>
                            <p class="section-kicker">بوابة الطالب</p>
                            <h1 class="mt-3 text-2xl font-bold lg:text-3xl">{{ $heading ?? 'الرئيسية' }}</h1>
                            @isset($subheading)
                                <p class="mt-3 max-w-3xl text-sm leading-8 text-[var(--color-ink-700)]">{{ $subheading }}</p>
                            @endisset
                        </div>

                        <div class="flex flex-wrap items-center gap-2 lg:justify-end">
                            <x-theme.toggle />

                            <a href="{{ route('student.cart.index') }}" class="portal-nav-utility">
                                <span>السلة</span>
                                @if ($cartCount > 0)
                                    <span class="inline-flex min-w-7 items-center justify-center rounded-full bg-[var(--color-brand-700)] px-2 py-1 text-xs font-bold text-white">{{ $cartCount }}</span>
                                @endif
                            </a>

                            <details class="relative">
                                <summary class="portal-nav-utility list-none cursor-pointer" aria-label="روابط الحساب الإضافية">روابط الحساب</summary>
                                <div class="portal-menu-panel absolute right-0 top-full z-30 mt-3">
                                    <div class="grid gap-2">
                                        @foreach ($moreLinks as $link)
                                            <a href="{{ $link['href'] }}" class="portal-menu-link">{{ $link['label'] }}</a>
                                        @endforeach
                                    </div>
                                </div>
                            </details>

                            <span class="text-xs font-semibold text-[var(--color-ink-500)]" title="نسخة إنجليزية ستتوفر لاحقًا">
                                English قريبًا
                            </span>

                            <form method="POST" action="{{ route('student.logout') }}">
                                @csrf
                                <button type="submit" class="btn-primary !px-4 !py-2">خروج</button>
                            </form>
                        </div>
                    </div>

                    <nav class="grid gap-2 sm:grid-cols-2 xl:grid-cols-6" aria-label="التنقل الرئيسي للطالب">
                        @foreach ($items as $item)
                            <a href="{{ $item['href'] }}" @class([
                                'portal-nav-link',
                                'portal-nav-link--active' => $item['active'],
                            ])>
                                <span class="portal-nav-link__icon" aria-hidden="true">{{ $navIcons[$item['label']] ?? '•' }}</span>
                                <span>{{ $item['label'] }}</span>
                            </a>
                        @endforeach
                    </nav>
                </div>
            </div>
        </header>

        <x-flash />

        <main id="student-main" tabindex="-1" class="space-y-6" aria-label="محتوى الصفحة">
            {{ $slot }}
        </main>
    </div>

    @livewire('shared.support-widget')
    @livewireScripts
    @stack('scripts')
</body>
</html>
