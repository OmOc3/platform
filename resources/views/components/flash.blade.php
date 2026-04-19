@if (session('status'))
    <div class="rounded-3xl bg-[color-mix(in_oklch,var(--color-brand-100)_75%,white)] px-5 py-4 text-sm font-semibold text-[var(--color-brand-700)]">
        {{ session('status') }}
    </div>
@endif
