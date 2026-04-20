<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? $platformBrand['name'] }}</title>
    <x-font-links />
    <x-theme.init-script />
    @livewireStyles
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="surface-shell">
    <a href="#public-main" class="skip-link">تخطَّ إلى المحتوى</a>
    <x-theme.toggle class="theme-toggle--floating fixed left-5 top-5 z-50" />

    <main id="public-main" tabindex="-1" class="mx-auto max-w-7xl px-2 py-4 lg:px-4">
        {{ $slot }}
    </main>

    @livewire('shared.support-widget')
    @livewireScripts
</body>
</html>
