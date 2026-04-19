<x-layouts.admin title="إضافة مسار" heading="إضافة مسار" subheading="إنشاء مسار أكاديمي مرتبط بصف دراسي.">
    <section class="panel-tight">
        <form method="POST" action="{{ route('admin.tracks.store') }}">
            @include('admin.academic.tracks._form')
        </form>
    </section>
</x-layouts.admin>
