<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? $platformBrand['name'] }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="surface-shell">
    <div class="mx-auto flex min-h-screen max-w-7xl items-center justify-center px-6 py-10">
        {{ $slot }}
    </div>
</body>
</html>
