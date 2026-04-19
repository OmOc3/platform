<x-layouts.admin title="إضافة محتوى" heading="إضافة محتوى" subheading="أنشئ محاضرة أو مراجعة جديدة مع ربطها بالصف والقسم وسعر الشراء عند الحاجة.">
    <section class="panel-tight">
        <form method="POST" action="{{ route('admin.lectures.store') }}">
            @include('admin.academic.lectures._form')
        </form>
    </section>
</x-layouts.admin>
