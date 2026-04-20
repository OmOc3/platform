@props([
    'title',
    'description',
])

<div class="panel-tight">
    <p class="section-kicker">لا يوجد محتوى</p>
    <h3 class="mt-3 text-lg font-bold">{{ $title }}</h3>
    <p class="mt-3 max-w-2xl text-sm leading-8 text-[var(--color-ink-700)]">{{ $description }}</p>
</div>
