<x-layouts.admin title="إضافة كتاب" heading="إضافة كتاب" subheading="أدخل بيانات الكتاب، حالة التوفر، والمخزون المبدئي.">
    <section class="panel-tight">
        <form method="POST" action="{{ route('admin.books.store') }}">
            @include('admin.commerce.books._form')
        </form>
    </section>
</x-layouts.admin>
