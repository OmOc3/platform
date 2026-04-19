<x-layouts.admin title="تعديل قسم المحاضرات" heading="تعديل قسم المحاضرات" subheading="تحديث الربط والوصف والترتيب لهذا القسم.">
    <section class="panel-tight">
        <form method="POST" action="{{ route('admin.lecture-sections.update', $section) }}">
            @include('admin.academic.lecture-sections._form')
        </form>
    </section>
</x-layouts.admin>
