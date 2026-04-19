<x-layouts.admin title="إضافة باقة" heading="إضافة باقة" subheading="أنشئ باقة جديدة وحدد العناصر الداخلة بداخلها وقاعدة التعارض الخاصة بها.">
    <section class="panel-tight">
        <form method="POST" action="{{ route('admin.packages.store') }}">
            @include('admin.commerce.packages._form')
        </form>
    </section>
</x-layouts.admin>
