<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ ($title ?? 'بوابة الطالب').' - '.$platformBrand['name'] }}</title>
    <x-theme.init-script />
    @livewireStyles
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="surface-shell">
    @php($student = auth('student')->user())
    @php($items = app(\App\Shared\Support\Navigation\StudentNavigation::class)->items())

    <div class="mx-auto max-w-7xl px-4 py-4 lg:px-6">
        <header class="panel mb-6 overflow-hidden px-5 py-5">
            <div class="grid gap-5 lg:grid-cols-[1.2fr_0.8fr] lg:items-center">
                <div>
                    <p class="font-display text-2xl text-[var(--color-brand-700)]">{{ $platformBrand['name'] }}</p>
                    <h1 class="mt-3 text-2xl font-bold">{{ $heading ?? 'بوابة الطالب' }}</h1>
                    @isset($subheading)
                        <p class="mt-3 max-w-2xl text-sm leading-8 text-[var(--color-ink-700)]">{{ $subheading }}</p>
                    @endisset
                </div>

                <div class="surface-inset rounded-[2rem] p-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-ink-500)]">بيانات الحساب</p>
                    <div class="mt-3 grid gap-3 sm:grid-cols-3">
                        <div>
                            <p class="text-xs text-[var(--color-ink-500)]">الطالب</p>
                            <p class="mt-1 font-semibold">{{ $student?->name }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-[var(--color-ink-500)]">الرقم</p>
                            <p class="mt-1 font-semibold">{{ $student?->student_number }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-[var(--color-ink-500)]">الحالة</p>
                            <p class="mt-1 font-semibold">{{ $student?->status?->value }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-5 flex flex-col gap-4 border-t border-[var(--color-border-soft)] pt-4">
                <nav class="flex flex-wrap gap-2">
                    @foreach ($items as $item)
                        <a href="{{ $item['href'] }}"
                           @class([
                               'rounded-full px-4 py-2 text-sm font-semibold transition',
                               'bg-[var(--color-primary-bg)] text-[var(--color-primary-foreground)] shadow-sm' => $item['active'],
                               'surface-inset text-[var(--color-ink-700)] hover:bg-[var(--color-panel-strong)]' => ! $item['active'],
                           ])>
                            {{ $item['label'] }}
                        </a>
                    @endforeach
                </nav>

                <div class="flex flex-wrap items-center gap-3">
                    <x-theme.toggle />
                    <a href="{{ route('student.cart.index') }}" class="btn-secondary">السلة</a>
                    <a href="{{ route('student.profile.show') }}" class="btn-secondary">الملف الشخصي</a>
                    <form method="POST" action="{{ route('student.logout') }}">
                        @csrf
                        <button type="submit" class="btn-primary">تسجيل الخروج</button>
                    </form>
                </div>
            </div>
        </header>

        <x-flash />

        {{ $slot }}
    </div>

    @livewire('shared.support-widget')
    @livewireScripts
    @stack('scripts')
</body>
</html>
