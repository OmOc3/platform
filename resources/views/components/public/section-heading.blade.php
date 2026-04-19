@props([
    'eyebrow' => null,
    'title',
    'description' => null,
])

<div class="max-w-2xl">
    @if ($eyebrow)
        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[var(--color-brand-700)]">{{ $eyebrow }}</p>
    @endif
    <h2 class="mt-3 text-3xl font-bold leading-tight lg:text-4xl">{{ $title }}</h2>
    @if ($description)
        <p class="mt-4 text-sm leading-8 text-[var(--color-ink-700)] lg:text-base">{{ $description }}</p>
    @endif
</div>
