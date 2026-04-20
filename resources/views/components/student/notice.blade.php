@props([
    'title',
    'body' => null,
    'description' => null,
    'tone' => 'default',
])

@php
    $classes = match ($tone) {
        'warning' => 'surface-tone surface-tone--warning',
        'violet' => 'surface-tone surface-tone--brand',
        'danger' => 'surface-tone surface-tone--danger',
        default => 'surface-tone bg-[var(--color-panel-strong)] text-[var(--color-ink-900)] border-[var(--color-border-soft)]',
    };
    $content = $body ?? $description;
@endphp

<article class="rounded-[1.4rem] px-5 py-4 {{ $classes }}">
    <p class="text-[0.72rem] font-semibold uppercase tracking-[0.18em] opacity-70">تنبيه</p>
    <h3 class="mt-2 text-sm font-bold">{{ $title }}</h3>
    <p class="mt-2 text-sm leading-8">{{ $content }}</p>
</article>
