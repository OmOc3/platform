<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ ($title ?? 'لوحة الإدارة').' - '.$platformBrand['name'] }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="surface-shell">
    @php($admin = auth('admin')->user())
    @php($menuSections = app(\App\Shared\Support\Navigation\AdminNavigation::class)->sections($admin))

    <div class="mx-auto grid min-h-screen max-w-[1600px] gap-6 px-4 py-4 lg:grid-cols-[300px_minmax(0,1fr)] lg:px-6">
        <aside class="panel hidden h-[calc(100vh-2rem)] overflow-y-auto p-5 lg:block">
            <div class="space-y-2 border-b border-[color-mix(in_oklch,var(--color-brand-100)_85%,white)] pb-5">
                <p class="font-display text-2xl text-[var(--color-brand-700)]">{{ $platformBrand['name'] }}</p>
                <p class="text-sm leading-7 text-[var(--color-ink-700)]">{{ $platformBrand['tagline'] }}</p>
            </div>

            <nav class="mt-6 space-y-5">
                @foreach ($menuSections as $section)
                    <div class="space-y-2">
                        <p class="px-3 text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-ink-500)]">{{ $section['label'] }}</p>
                        <div class="space-y-1 rounded-3xl bg-[var(--color-brand-50)] p-2">
                            @foreach ($section['items'] as $item)
                                <a href="{{ route($item['route']) }}"
                                   @class([
                                       'sidebar-link',
                                       'sidebar-link-active' => request()->routeIs($item['route']) || str_starts_with((string) optional(request()->route())->getName(), $item['route'].'.'),
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
                    <div class="rounded-full bg-[var(--color-brand-50)] px-4 py-2 text-sm text-[var(--color-ink-700)]">
                        {{ $admin?->name }}
                    </div>
                    <form method="POST" action="{{ route('admin.logout') }}">
                        @csrf
                        <button type="submit" class="btn-secondary">تسجيل الخروج</button>
                    </form>
                </div>
            </header>

            <x-flash />

            {{ $slot }}
        </main>
    </div>
</body>
</html>
