<x-layouts.student title="الكتب" heading="مكتبة الكتب" subheading="استعرض الكتب المتاحة للطلب الفوري أو المسبق، وراجع المخزون قبل الإضافة إلى السلة.">
    <section class="space-y-6">
        <form method="GET" class="panel-tight grid gap-3 lg:grid-cols-[1fr_auto]">
            <input type="search" name="search" value="{{ request('search') }}" class="form-input" placeholder="ابحث باسم الكتاب أو الوصف المختصر">
            <button class="btn-secondary">بحث</button>
        </form>

        <div class="grid gap-4 xl:grid-cols-2">
            @forelse ($books as $book)
                <article class="panel-tight">
                    <div class="flex flex-wrap items-center gap-3">
                        <x-admin.status-badge :label="$book->availability_status->value" :tone="$book->availability_status->value === 'in_stock' ? 'success' : ($book->availability_status->value === 'pre_order' ? 'warning' : 'danger')" />
                        @if ($book->cover_badge)
                            <x-admin.status-badge :label="$book->cover_badge" />
                        @endif
                    </div>

                    <h2 class="mt-4 text-xl font-bold">{{ $book->product?->name_ar }}</h2>
                    <p class="mt-3 text-sm leading-8 text-[var(--color-ink-700)]">{{ $book->product?->teaser }}</p>

                    <div class="mt-6 grid gap-3 sm:grid-cols-3">
                        <div class="rounded-[1.4rem] bg-[var(--color-brand-50)] p-4">
                            <p class="text-xs text-[var(--color-ink-500)]">السعر</p>
                            <p class="mt-2 font-semibold">{{ number_format($book->product?->price_amount ?? 0) }} {{ $book->product?->currency }}</p>
                        </div>
                        <div class="rounded-[1.4rem] bg-[var(--color-brand-50)] p-4">
                            <p class="text-xs text-[var(--color-ink-500)]">الصفحات</p>
                            <p class="mt-2 font-semibold">{{ $book->page_count ?: '—' }}</p>
                        </div>
                        <div class="rounded-[1.4rem] bg-[var(--color-brand-50)] p-4">
                            <p class="text-xs text-[var(--color-ink-500)]">المخزون</p>
                            <p class="mt-2 font-semibold">{{ $book->stock_quantity }}</p>
                        </div>
                    </div>

                    <div class="mt-6 flex flex-wrap gap-3">
                        <a href="{{ route('student.books.show', $book) }}" class="btn-primary">عرض التفاصيل</a>
                        @if ($book->availability_status->value !== 'sold_out' && $book->product)
                            <form method="POST" action="{{ route('student.cart.store') }}">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $book->product->id }}">
                                <button class="btn-secondary">أضف إلى السلة</button>
                            </form>
                        @endif
                    </div>
                </article>
            @empty
                <x-student.empty-state title="لا توجد كتب متاحة الآن" description="عند نشر أي كتاب جديد سيظهر هنا تلقائيًا." />
            @endforelse
        </div>

        <div class="px-2">
            {{ $books->links() }}
        </div>
    </section>
</x-layouts.student>
