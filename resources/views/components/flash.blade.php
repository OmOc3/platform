@if (session('status'))
    <div class="rounded-[1.3rem] border border-[var(--color-border-soft)] bg-[var(--color-panel-strong)] px-5 py-4 text-sm font-semibold text-[var(--color-brand-700)] shadow-[var(--shadow-line)]">
        {{ session('status') }}
    </div>
@endif
