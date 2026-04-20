@props(['current' => 'profile'])

@php
    $links = [
        ['key' => 'profile', 'label' => 'الملف الشخصي', 'href' => route('student.profile.show')],
        ['key' => 'payments', 'label' => 'مدفوعات المحاضرات', 'href' => route('student.payments.index')],
        ['key' => 'book-orders', 'label' => 'مدفوعات الكتب', 'href' => route('student.book-orders.index')],
        ['key' => 'attendance', 'label' => 'حضور السنتر', 'href' => route('student.attendance.index')],
    ];
@endphp

<nav class="account-nav" aria-label="تنقل صفحات الحساب">
    @foreach ($links as $link)
        <a
            href="{{ $link['href'] }}"
            @class([
                'account-nav__link',
                'account-nav__link--active' => $current === $link['key'],
            ])
        >
            {{ $link['label'] }}
        </a>
    @endforeach
</nav>
