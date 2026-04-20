@props([
    'label',
    'value',
    'description' => null,
])

<article class="portal-summary-card">
    <p class="portal-summary-card__label">{{ $label }}</p>
    <p class="portal-summary-card__value">{{ $value }}</p>
    @if ($description)
        <p class="mt-3 text-sm leading-7 text-[var(--color-ink-700)]">{{ $description }}</p>
    @endif
</article>
