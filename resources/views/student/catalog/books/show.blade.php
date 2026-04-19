<x-layouts.student :title="$book->product?->name_ar" :heading="$book->product?->name_ar" subheading="صفحة تفصيلية للكتاب مع حالة المخزون وإمكانية إضافته إلى السلة.">
    <section class="grid gap-6 xl:grid-cols-[1.15fr_0.85fr]">
        <article class="panel-tight">
            <div class="flex flex-wrap items-center gap-3">
                <x-admin.status-badge :label="$book->availability_status->value" :tone="$book->availability_status->value === 'in_stock' ? 'success' : ($book->availability_status->value === 'pre_order' ? 'warning' : 'danger')" />
                @if ($book->cover_badge)
                    <x-admin.status-badge :label="$book->cover_badge" />
                @endif
            </div>

            <p class="mt-6 text-base leading-9 text-[var(--color-ink-700)]">{{ $book->product?->description }}</p>

            <dl class="mt-8 grid gap-4 sm:grid-cols-3">
                <div class="rounded-[1.6rem] bg-[var(--color-brand-50)] p-4">
                    <dt class="text-xs text-[var(--color-ink-500)]">المؤلف</dt>
                    <dd class="mt-2 font-semibold">{{ $book->author_name ?: '—' }}</dd>
                </div>
                <div class="rounded-[1.6rem] bg-[var(--color-brand-50)] p-4">
                    <dt class="text-xs text-[var(--color-ink-500)]">الصفحات</dt>
                    <dd class="mt-2 font-semibold">{{ $book->page_count ?: '—' }}</dd>
                </div>
                <div class="rounded-[1.6rem] bg-[var(--color-brand-50)] p-4">
                    <dt class="text-xs text-[var(--color-ink-500)]">المخزون الحالي</dt>
                    <dd class="mt-2 font-semibold">{{ $book->stock_quantity }}</dd>
                </div>
            </dl>
        </article>

        <aside class="panel-tight">
            <p class="text-sm font-semibold text-[var(--color-brand-700)]">الطلب والشحن</p>
            <p class="mt-4 text-sm leading-8 text-[var(--color-ink-700)]">
                السعر الحالي {{ number_format($book->product?->price_amount ?? 0) }} {{ $book->product?->currency }}.
                حالة الكتاب الآن: {{ $book->availability_status->value }}.
            </p>

            <div class="mt-6 flex flex-col gap-3">
                @if ($book->availability_status->value !== 'sold_out' && $book->product)
                    <form method="POST" action="{{ route('student.cart.store') }}">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $book->product->id }}">
                        <button class="btn-primary">أضف الكتاب إلى السلة</button>
                    </form>
                @else
                    <button type="button" class="btn-secondary !cursor-default !opacity-70">نفد المخزون الحالي</button>
                @endif
                <a href="{{ route('student.books.index') }}" class="btn-secondary">العودة إلى الكتب</a>
            </div>
        </aside>
    </section>
</x-layouts.student>
