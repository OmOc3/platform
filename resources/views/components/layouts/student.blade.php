<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ ($title ?? 'بوابة الطالب').' - '.$platformBrand['name'] }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="surface-shell">
    @php($items = app(\App\Shared\Support\Navigation\StudentNavigation::class)->items())

    <div class="mx-auto max-w-7xl px-4 py-4 lg:px-6">
        <header class="panel mb-6 px-5 py-4">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="font-display text-2xl text-[var(--color-brand-700)]">{{ $platformBrand['name'] }}</p>
                    <p class="mt-2 text-sm text-[var(--color-ink-700)]">نموذج واجهة الطالب العربية للمحاضرات والباقات والكتب والدعم.</p>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <button class="btn-secondary">السلة</button>
                    <button class="btn-secondary">الملف الشخصي</button>
                    <button class="btn-primary">الدعم الفني</button>
                </div>
            </div>

            <nav class="mt-5 flex flex-wrap gap-2 border-t border-[color-mix(in_oklch,var(--color-brand-100)_80%,white)] pt-4">
                @foreach ($items as $item)
                    <a href="{{ $item['href'] }}" class="rounded-full bg-[var(--color-brand-50)] px-4 py-2 text-sm font-semibold text-[var(--color-ink-700)] hover:bg-[var(--color-brand-100)]">
                        {{ $item['label'] }}
                    </a>
                @endforeach
            </nav>
        </header>

        {{ $slot }}

        <a href="#" class="fixed bottom-5 left-5 inline-flex items-center rounded-full bg-[var(--color-brand-700)] px-5 py-3 text-sm font-semibold text-white shadow-[0_20px_50px_rgba(71,58,29,0.2)]">
            تواصل مع الدعم
        </a>
    </div>
</body>
</html>
