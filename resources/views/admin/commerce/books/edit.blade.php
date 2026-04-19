<x-layouts.admin title="تعديل كتاب" heading="تعديل كتاب" subheading="تحديث بيانات الكتاب والمخزون وحالة الإتاحة.">
    <section class="panel-tight">
        <form method="POST" action="{{ route('admin.books.update', $book) }}">
            @include('admin.commerce.books._form')
        </form>
    </section>
</x-layouts.admin>
