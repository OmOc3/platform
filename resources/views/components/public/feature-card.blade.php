@props([
    'title',
    'description',
])

<article class="surface-card rounded-[1.5rem] p-6">
    <p class="section-kicker">تفصيلة تشغيلية</p>
    <p class="mt-3 text-lg font-bold text-[var(--color-ink-900)]">{{ $title }}</p>
    <p class="mt-4 text-sm leading-8 text-[var(--color-ink-700)]">{{ $description }}</p>
</article>
