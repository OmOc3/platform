@props([
    'title',
    'description',
])

<div class="panel-tight text-center">
    <h3 class="text-lg font-bold">{{ $title }}</h3>
    <p class="mt-3 text-sm leading-8 text-[var(--color-ink-700)]">{{ $description }}</p>
</div>
