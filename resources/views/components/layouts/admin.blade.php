<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ ($title ?? 'لوحة الإدارة').' - '.$platformBrand['name'] }}</title>
    <x-theme.init-script />
    @livewireStyles
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="surface-shell">
    @php($admin = auth('admin')->user())
    @php($menuSections = app(\App\Shared\Support\Navigation\AdminNavigation::class)->sections($admin))
    @php($isActiveRoute = fn (string $route) => request()->routeIs($route) || str_starts_with((string) optional(request()->route())->getName(), $route.'.'))

    <div class="mx-auto grid min-h-screen max-w-[1600px] gap-6 px-4 py-4 lg:grid-cols-[300px_minmax(0,1fr)] lg:px-6">
        <aside class="panel hidden h-[calc(100vh-2rem)] overflow-y-auto p-5 lg:block" aria-label="التنقل الإداري الرئيسي">
            <div class="space-y-2 border-b border-[var(--color-border-soft)] pb-5">
                <p class="font-display text-2xl text-[var(--color-brand-700)]">{{ $platformBrand['name'] }}</p>
                <p class="text-sm leading-7 text-[var(--color-ink-700)]">{{ $platformBrand['tagline'] }}</p>
            </div>

            <nav class="mt-6 space-y-5">
                @foreach ($menuSections as $section)
                    <div class="space-y-2">
                        <p class="px-3 text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-ink-500)]">{{ $section['label'] }}</p>
                        <div class="surface-inset space-y-1 rounded-3xl p-2">
                            @foreach ($section['items'] as $item)
                                <a href="{{ route($item['route']) }}"
                                   @class([
                                       'sidebar-link',
                                       'sidebar-link-active' => $isActiveRoute($item['route']),
                                   ])>
                                    <span>{{ $item['label'] }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </nav>
        </aside>

        <main class="space-y-6 py-1">
            <header class="panel flex flex-col gap-4 p-5 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-sm font-semibold text-[var(--color-brand-700)]">لوحة الإدارة</p>
                    <h1 class="mt-2 text-2xl font-bold">{{ $heading ?? 'إدارة المنصة' }}</h1>
                    @isset($subheading)
                        <p class="mt-2 text-sm leading-7 text-[var(--color-ink-700)]">{{ $subheading }}</p>
                    @endisset
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <x-theme.toggle />

                    <div class="surface-inset rounded-full px-4 py-2 text-sm text-[var(--color-ink-700)]">
                        {{ $admin?->name }}
                    </div>

                    <form method="POST" action="{{ route('admin.logout') }}">
                        @csrf
                        <button type="submit" class="btn-secondary">تسجيل الخروج</button>
                    </form>
                </div>
            </header>

            <details class="admin-mobile-nav lg:hidden">
                <summary class="admin-mobile-nav__trigger">
                    <span class="admin-mobile-nav__eyebrow">التنقل الإداري</span>
                    <span class="admin-mobile-nav__title">أقسام الإدارة</span>
                    <span class="admin-mobile-nav__hint">افتح القائمة السريعة للصفحات الأساسية</span>
                </summary>

                <div class="admin-mobile-nav__panel">
                    @foreach ($menuSections as $section)
                        <section class="admin-mobile-nav__section">
                            <p class="admin-mobile-nav__section-label">{{ $section['label'] }}</p>
                            <div class="grid gap-2">
                                @foreach ($section['items'] as $item)
                                    <a href="{{ route($item['route']) }}"
                                       @class([
                                           'admin-mobile-nav__link',
                                           'admin-mobile-nav__link--active' => $isActiveRoute($item['route']),
                                       ])>
                                        <span>{{ $item['label'] }}</span>
                                    </a>
                                @endforeach
                            </div>
                        </section>
                    @endforeach
                </div>
            </details>

            <x-flash />

            {{ $slot }}
        </main>
    </div>

    @livewireScripts
    @stack('scripts')
</body>
</html>
