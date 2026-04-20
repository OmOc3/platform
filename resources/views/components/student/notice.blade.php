@props([
    'title',
    'body',
    'tone' => 'default',
])

@php
    $classes = match ($tone) {
        'warning' => 'bg-[color-mix(in_oklch,var(--color-warning)_16%,white)] text-[color-mix(in_oklch,var(--color-warning)_85%,black)]',
        'violet' => 'bg-[color-mix(in_oklch,var(--color-violet-100)_60%,white)] text-[var(--color-violet-700)]',
        default => 'bg-[var(--color-brand-50)] text-[var(--color-brand-700)]',
    };
@endphp

<article class="rounded-[2rem] px-5 py-4 shadow-[0_18px_40px_rgba(71,58,29,0.06)] {{ $classes }}">
    <h3 class="text-sm font-bold">{{ $title }}</h3>
    <p class="mt-2 text-sm leading-8">{{ $body }}</p>
</article>
