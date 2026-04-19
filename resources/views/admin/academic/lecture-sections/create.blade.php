<x-layouts.admin title="إضافة قسم محاضرات" heading="إضافة قسم محاضرات" subheading="تعريف قسم تشغيلي جديد للمحاضرات والمراجعات داخل الكتالوج.">
    <section class="panel-tight">
        <form method="POST" action="{{ route('admin.lecture-sections.store') }}">
            @include('admin.academic.lecture-sections._form')
        </form>
    </section>
</x-layouts.admin>
