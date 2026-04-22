@props([
    'src',
    'alt',
    'variant' => 'default',
    'priority' => false,
    'sizes' => null,
])

@php
    $dimensions = match ($variant) {
        'book' => ['width' => 720, 'height' => 960],
        default => ['width' => 960, 'height' => 720],
    };
@endphp

<img
    src="{{ $src }}"
    alt="{{ $alt }}"
    width="{{ $dimensions['width'] }}"
    height="{{ $dimensions['height'] }}"
    loading="{{ $priority ? 'eager' : 'lazy' }}"
    decoding="async"
    @if ($priority)
        fetchpriority="high"
    @endif
    @if ($sizes)
        sizes="{{ $sizes }}"
    @endif
    {{ $attributes->class('h-full w-full object-cover') }}
>
