<x-layouts.admin title="تعديل صف" heading="تعديل صف" subheading="تحديث بيانات الصف وترتيبه ضمن الهيكل الأكاديمي.">
    <section class="panel-tight">
        <form method="POST" action="{{ route('admin.grades.update', $grade) }}">
            @include('admin.academic.grades._form')
        </form>
    </section>
</x-layouts.admin>
