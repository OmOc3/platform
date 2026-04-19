<x-layouts.admin title="تعديل مشرف" heading="تعديل مشرف" subheading="تحديث بيانات المشرف وصلاحياته التشغيلية.">
    <section class="panel-tight">
        <form method="POST" action="{{ route('admin.admins.update', $adminUser) }}">
            @include('admin.identity.admins._form')
        </form>
    </section>
</x-layouts.admin>
