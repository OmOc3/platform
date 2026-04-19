<x-layouts.admin title="إضافة مشرف" heading="إضافة مشرف" subheading="إنشاء عضو جديد في فريق الإدارة مع ربط الدور المناسب.">
    <section class="panel-tight">
        <form method="POST" action="{{ route('admin.admins.store') }}">
            @include('admin.identity.admins._form')
        </form>
    </section>
</x-layouts.admin>
