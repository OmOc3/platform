<x-layouts.admin title="تعديل اختبار" heading="تعديل اختبار" subheading="حدّث العنوان، المدة، الربط الأكاديمي، وحالة النشر لهذا الاختبار.">
    <section class="panel-tight">
        <form method="POST" action="{{ route('admin.exams.update', $exam) }}">
            @include('admin.academic.exams._form')
        </form>
    </section>
</x-layouts.admin>
