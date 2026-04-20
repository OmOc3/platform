@props([
    'label',
    'value',
    'description' => null,
])

<article {{ $attributes->class('admin-metric-card') }}>
    <p class="admin-metric-card__eyebrow">{{ $label }}</p>
    <span class="admin-metric-card__value">{{ $value }}</span>

    @if ($description)
        <p class="admin-metric-card__description">{{ $description }}</p>
    @endif
</article>
