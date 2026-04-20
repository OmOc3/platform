@props([
    'label',
    'value',
    'description' => null,
])

<article class="portal-summary-card">
    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-ink-500)]">{{ $label }}</p>
    <p class="mt-4 text-3xl font-bold text-[var(--color-brand-700)]">{{ $value }}</p>
    @if ($description)
        <p class="mt-3 text-sm leading-7 text-[var(--color-ink-700)]">{{ $description }}</p>
    @endif
</article>
