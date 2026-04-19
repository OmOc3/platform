<x-layouts.admin title="تعديل قسم المنهج" heading="تعديل قسم المنهج" subheading="تحديث بيانات القسم وترتيبه ضمن البنية الأكاديمية الحالية.">
    <section class="panel-tight">
        <form method="POST" action="{{ route('admin.curriculum-sections.update', $section) }}">
            @include('admin.academic.curriculum-sections._form')
        </form>
    </section>
</x-layouts.admin>
