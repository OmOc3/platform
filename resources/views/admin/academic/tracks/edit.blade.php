<x-layouts.admin title="تعديل مسار" heading="تعديل مسار" subheading="تحديث ربط المسار بالصف والهيكل الأكاديمي.">
    <section class="panel-tight">
        <form method="POST" action="{{ route('admin.tracks.update', $track) }}">
            @include('admin.academic.tracks._form')
        </form>
    </section>
</x-layouts.admin>
