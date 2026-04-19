@props([
    'label',
    'value',
])

<article class="panel-tight">
    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-ink-500)]">{{ $label }}</p>
    <p class="mt-4 text-3xl font-bold text-[var(--color-brand-700)]">{{ $value }}</p>
</article>
