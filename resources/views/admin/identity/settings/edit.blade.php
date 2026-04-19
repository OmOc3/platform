<x-layouts.admin title="تعديل إعداد" heading="تعديل إعداد" subheading="تحديث القيم التشغيلية والقيم العامة للمنصة.">
    <section class="panel-tight">
        <form method="POST" action="{{ route('admin.settings.update', $setting) }}">
            @include('admin.identity.settings._form')
        </form>
    </section>
</x-layouts.admin>
