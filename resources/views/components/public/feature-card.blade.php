@props([
    'title',
    'description',
])

<article class="rounded-[2rem] bg-white p-6 shadow-[0_20px_50px_rgba(71,58,29,0.07)] ring-1 ring-[color-mix(in_oklch,var(--color-brand-100)_85%,white)]">
    <p class="text-sm font-semibold text-[var(--color-brand-700)]">{{ $title }}</p>
    <p class="mt-4 text-sm leading-8 text-[var(--color-ink-700)]">{{ $description }}</p>
</article>
