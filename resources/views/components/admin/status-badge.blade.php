@props([
    'label',
    'tone' => 'neutral',
])

@php
    $classes = match ($tone) {
        'success' => 'status-pill status-pill--success',
        'warning' => 'status-pill status-pill--warning',
        'danger' => 'status-pill status-pill--danger',
        default => 'status-pill status-pill--brand',
    };
@endphp

<span {{ $attributes->class($classes) }}>
    {{ $label }}
</span>
