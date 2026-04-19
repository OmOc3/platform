@props([
    'title',
    'description' => null,
])

<section class="table-shell">
    <div class="flex flex-col gap-4 px-5 py-5 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <h2 class="text-lg font-bold">{{ $title }}</h2>
            @if ($description)
                <p class="mt-2 text-sm leading-7 text-[var(--color-ink-700)]">{{ $description }}</p>
            @endif
        </div>
        @isset($actions)
            <div class="flex flex-wrap gap-2">
                {{ $actions }}
            </div>
        @endisset
    </div>

    @isset($filters)
        <div class="border-t border-[color-mix(in_oklch,var(--color-brand-100)_80%,white)] px-5 py-4">
            {{ $filters }}
        </div>
    @endisset

    <div class="overflow-x-auto">
        {{ $slot }}
    </div>
</section>
