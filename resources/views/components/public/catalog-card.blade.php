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

<article class="flex h-full flex-col rounded-[1.5rem] {{ $toneClass }} p-5">
    <div class="flex items-start justify-between gap-4">
        <div>
            <p class="section-kicker">{{ $tone === 'book' ? 'إصدار مطبوع' : 'عرض أكاديمي' }}</p>
            <p class="mt-3 text-lg font-bold text-[var(--color-ink-900)]">{{ $product->name_ar }}</p>
            <p class="mt-3 text-sm leading-7 text-[var(--color-ink-700)]">{{ $product->teaser }}</p>
        </div>
        <span class="status-pill status-pill--brand">
            {{ number_format($product->price_amount) }} ج
        </span>
    </div>

    <div class="mt-5 flex flex-wrap gap-2 text-xs text-[var(--color-ink-500)]">
        @foreach ($meta as $value)
            <span class="surface-chip rounded-full px-3 py-2">{{ $value }}</span>
        @endforeach
    </div>
</article>
