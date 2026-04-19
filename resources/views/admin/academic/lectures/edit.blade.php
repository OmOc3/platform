<x-layouts.admin title="تعديل المحتوى" heading="تعديل المحتوى" subheading="تحديث بيانات النشر والتصنيف والتسعير لهذا العنصر الأكاديمي.">
    <section class="panel-tight">
        <form method="POST" action="{{ route('admin.lectures.update', $lecture) }}">
            @include('admin.academic.lectures._form')
        </form>
    </section>
</x-layouts.admin>
