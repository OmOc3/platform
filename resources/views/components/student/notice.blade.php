@props([
    'title',
    'body',
    'tone' => 'default',
])

@php
    $classes = $tone === 'warning'
        ? 'bg-[color-mix(in_oklch,var(--color-warning)_16%,white)] text-[color-mix(in_oklch,var(--color-warning)_85%,black)]'
        : 'bg-[var(--color-brand-50)] text-[var(--color-brand-700)]';
@endphp

<article class="rounded-[2rem] px-5 py-4 {{ $classes }}">
    <h3 class="text-sm font-bold">{{ $title }}</h3>
    <p class="mt-2 text-sm leading-8">{{ $body }}</p>
</article>
