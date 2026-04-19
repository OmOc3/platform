<x-layouts.admin title="إضافة اختبار" heading="إضافة اختبار" subheading="أنشئ اختبارًا جديدًا واربطه بمحاضرة إن لزم ذلك.">
    <section class="panel-tight">
        <form method="POST" action="{{ route('admin.exams.store') }}">
            @include('admin.academic.exams._form')
        </form>
    </section>
</x-layouts.admin>
