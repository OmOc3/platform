@props([
    'product',
    'meta',
    'tone' => 'package',
])

@php
    $toneClass = $tone === 'book'
        ? 'surface-card-soft'
        : 'surface-card';
@endphp

<article class="flex h-full flex-col rounded-[2rem] {{ $toneClass }} p-5">
    <div class="flex items-start justify-between gap-3">
        <div>
            <p class="text-sm font-semibold text-[var(--color-brand-700)]">{{ $product->name_ar }}</p>
            <p class="mt-3 text-sm leading-7 text-[var(--color-ink-700)]">{{ $product->teaser }}</p>
        </div>
        <span class="rounded-full bg-[var(--color-brand-50)] px-3 py-1 text-xs font-semibold text-[var(--color-brand-700)]">
            {{ number_format($product->price_amount) }} ج
        </span>
    </div>

    <div class="mt-5 flex flex-wrap gap-2 text-xs text-[var(--color-ink-500)]">
        @foreach ($meta as $value)
            <span class="surface-chip rounded-full px-3 py-2">{{ $value }}</span>
        @endforeach
    </div>
</article>
