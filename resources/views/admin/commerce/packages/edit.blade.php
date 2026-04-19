<x-layouts.admin title="تعديل باقة" heading="تعديل باقة" subheading="حدّث بيانات الباقة ومحتوياتها وسياسة التعارض الخاصة بها.">
    <section class="panel-tight">
        <form method="POST" action="{{ route('admin.packages.update', $package) }}">
            @include('admin.commerce.packages._form')
        </form>
    </section>
</x-layouts.admin>
