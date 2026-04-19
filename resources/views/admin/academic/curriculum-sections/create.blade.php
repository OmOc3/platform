<x-layouts.admin title="إضافة قسم منهج" heading="إضافة قسم منهج" subheading="أنشئ قسمًا جديدًا لربط المحاضرات والمراجعات به داخل الكتالوج.">
    <section class="panel-tight">
        <form method="POST" action="{{ route('admin.curriculum-sections.store') }}">
            @include('admin.academic.curriculum-sections._form')
        </form>
    </section>
</x-layouts.admin>
