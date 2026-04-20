@props([
    'label' => 'المظهر',
])

<button
    type="button"
    data-theme-toggle
    aria-label="تبديل المظهر"
    aria-pressed="false"
    title="تبديل المظهر"
    {{ $attributes->class('theme-toggle') }}
>
    <span class="theme-toggle__indicator" aria-hidden="true">
        <span data-theme-icon-light>☀</span>
        <span data-theme-icon-dark>☾</span>
    </span>

    <span class="theme-toggle__content">
        <span class="theme-toggle__eyebrow">{{ $label }}</span>
        <span class="theme-toggle__label" data-theme-label>فاتح</span>
    </span>

    <span class="sr-only" data-theme-status>الوضع الحالي: فاتح</span>
</button>
