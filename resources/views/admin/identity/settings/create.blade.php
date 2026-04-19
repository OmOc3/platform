<x-layouts.admin title="إضافة إعداد" heading="إضافة إعداد" subheading="بناء سجل إعدادات قابل للتوسع بدلًا من منطق ثابت في الكود.">
    <section class="panel-tight">
        <form method="POST" action="{{ route('admin.settings.store') }}">
            @include('admin.identity.settings._form')
        </form>
    </section>
</x-layouts.admin>
