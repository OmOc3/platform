<x-layouts.admin title="إضافة صف" heading="إضافة صف" subheading="إنشاء صف دراسي لاستخدامه كأساس للمنهج والتتبع الأكاديمي.">
    <section class="panel-tight">
        <form method="POST" action="{{ route('admin.grades.store') }}">
            @include('admin.academic.grades._form')
        </form>
    </section>
</x-layouts.admin>
