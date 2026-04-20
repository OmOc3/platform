@props([
    'eyebrow' => null,
    'title',
    'description' => null,
])

<div class="max-w-3xl">
    @if ($eyebrow)
        <p class="section-kicker">{{ $eyebrow }}</p>
    @endif
    <h2 class="mt-3 font-display text-3xl leading-tight text-[var(--color-brand-700)] lg:text-4xl">{{ $title }}</h2>
    @if ($description)
        <p class="mt-4 text-sm leading-8 text-[var(--color-ink-700)] lg:text-base">{{ $description }}</p>
    @endif
</div>
