<div class="fixed bottom-5 left-5 z-40 w-[min(22rem,calc(100vw-2rem))]">
    <div
        id="support-widget-panel"
        @class([
            'support-widget__panel surface-card mb-3 rounded-[2rem] p-5 shadow-[0_24px_60px_rgba(71,58,29,0.18)]',
            'hidden' => ! $open,
        ])
        role="region"
        aria-label="خيارات الدعم السريع"
    >
        @if ($open)
            <p class="text-sm font-semibold text-[var(--color-brand-700)]">{{ $support['title'] }}</p>
            <p class="mt-3 text-sm leading-7 text-[var(--color-ink-700)]">{{ $support['description'] }}</p>
            <div class="mt-4 flex flex-wrap gap-2">
                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $brand['support_whatsapp']) }}" target="_blank" rel="noreferrer" class="btn-primary !px-4 !py-2">واتساب</a>
                <a href="tel:{{ $brand['support_phone'] }}" class="btn-secondary !px-4 !py-2">اتصال</a>
                @if ($student)
                    <a href="{{ route('student.complaints.index') }}" class="btn-secondary !px-4 !py-2">بوابة الدعم</a>
                @endif
            </div>
        @endif
    </div>

    <button
        type="button"
        wire:click="toggle"
        class="support-widget__toggle btn-primary w-full justify-between !rounded-[1.6rem] !px-5"
        aria-expanded="{{ $open ? 'true' : 'false' }}"
        aria-controls="support-widget-panel"
    >
        <span>{{ $support['cta_label'] }}</span>
        <span>{{ $open ? 'إخفاء الخيارات' : 'عرض الخيارات' }}</span>
    </button>
</div>
