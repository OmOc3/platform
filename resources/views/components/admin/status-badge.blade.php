@props([
    'label',
    'tone' => 'neutral',
])

@php
    $classes = match ($tone) {
        'success' => 'status-pill bg-[color-mix(in_oklch,var(--color-success)_18%,white)] text-[color-mix(in_oklch,var(--color-success)_70%,black)]',
        'warning' => 'status-pill bg-[color-mix(in_oklch,var(--color-warning)_20%,white)] text-[color-mix(in_oklch,var(--color-warning)_80%,black)]',
        'danger' => 'status-pill bg-[color-mix(in_oklch,var(--color-danger)_16%,white)] text-[color-mix(in_oklch,var(--color-danger)_75%,black)]',
        default => 'status-pill bg-[var(--color-brand-50)] text-[var(--color-brand-700)]',
    };
@endphp

<span {{ $attributes->class($classes) }}>
    {{ $label }}
</span>
