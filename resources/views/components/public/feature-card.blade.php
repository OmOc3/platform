@props([
    'title',
    'description',
])

<article class="surface-card rounded-[2rem] p-6">
    <p class="text-sm font-semibold text-[var(--color-brand-700)]">{{ $title }}</p>
    <p class="mt-4 text-sm leading-8 text-[var(--color-ink-700)]">{{ $description }}</p>
</article>
