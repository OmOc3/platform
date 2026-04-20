<x-layouts.student title="الكتب" heading="كتب" subheading="مكتبة الكتب المطبوعة داخل المنصة مع حالة المخزون والإضافة للسلة وبيانات الشحن المدعومة لكل إصدار.">
    <section class="space-y-6">
        <section class="panel-tight">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="section-kicker">مكتبة الكتب</p>
                    <h2 class="mt-2 text-2xl font-bold lg:text-3xl">كتب ومذكرات يمكن طلبها مباشرة من المتجر.</h2>
                    <p class="mt-3 max-w-3xl text-sm leading-8 text-[var(--color-ink-700)]">
                        كل بطاقة تعرض السعر، الصفحات، حالة المخزون، وما إذا كان الكتاب موجودًا بالفعل داخل السلة حتى لا تكرر الإضافة.
                    </p>
                </div>
                <a href="{{ route('student.cart.index') }}" class="btn-secondary">الانتقال إلى السلة</a>
            </div>

            <form method="GET" class="mt-6 grid gap-3 lg:grid-cols-[1fr_auto]">
                <input type="search" name="search" value="{{ request('search') }}" class="form-input" placeholder="ابحث باسم الكتاب أو الوصف المختصر" aria-label="ابحث في الكتب">
                <button class="btn-secondary">بحث</button>
            </form>
        </section>

        <div class="grid gap-4 xl:grid-cols-2">
            @forelse ($books as $row)
                @php($book = $row['book'])
                @php($inCart = $row['in_cart'])

                <article class="surface-card rounded-[2rem] p-5">
                    <div class="flex flex-col gap-5 lg:flex-row">
                        <div class="catalog-thumb catalog-thumb--book max-w-[11rem] shrink-0 lg:w-[11rem]">
                            @if ($book->product?->thumbnail_url)
                                <img src="{{ $book->product->thumbnail_url }}" alt="{{ $book->product?->name_ar }}" loading="lazy" decoding="async">
                            @else
                                <div class="catalog-thumb__fallback">
                                    <span>{{ $book->cover_badge ?: 'كتاب' }}</span>
                                    <strong>{{ $book->page_count ?: '—' }} صفحة</strong>
                                </div>
                            @endif
                        </div>

                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <x-admin.status-badge :label="$book->availability_status->label()" :tone="$book->availability_status->tone()" />
                                @if ($book->cover_badge)
                                    <x-admin.status-badge :label="$book->cover_badge" />
                                @endif
                                @if ($inCart)
                                    <x-admin.status-badge label="تمت الإضافة" />
                                @endif
                            </div>

                            <h2 class="mt-4 text-xl font-bold">{{ $book->product?->name_ar }}</h2>
                            <p class="mt-3 text-sm leading-8 text-[var(--color-ink-700)]">{{ $book->product?->teaser }}</p>

                            <div class="mt-5 grid gap-3 sm:grid-cols-3">
                                <div class="stat-tile">
                                    <p class="text-xs text-[var(--color-ink-500)]">السعر</p>
                                    <p class="mt-2 font-semibold">{{ number_format($book->product?->price_amount ?? 0) }} {{ $book->product?->currency }}</p>
                                </div>
                                <div class="stat-tile">
                                    <p class="text-xs text-[var(--color-ink-500)]">الصفحات</p>
                                    <p class="mt-2 font-semibold">{{ $book->page_count ?: '—' }}</p>
                                </div>
                                <div class="stat-tile">
                                    <p class="text-xs text-[var(--color-ink-500)]">المخزون</p>
                                    <p class="mt-2 font-semibold">{{ $book->stock_quantity }}</p>
                                </div>
                            </div>

                            <div class="mt-6 flex flex-wrap gap-3">
                                <a href="{{ route('student.books.show', $book) }}" class="btn-primary">عرض التفاصيل</a>

                                @if ($book->availability_status->value !== 'sold_out' && $book->product && ! $inCart)
                                    <form method="POST" action="{{ route('student.cart.store') }}">
                                        @csrf
                                        <input type="hidden" name="product_id" value="{{ $book->product->id }}">
                                        <button class="btn-secondary">أضف إلى السلة</button>
                                    </form>
                                @elseif ($inCart)
                                    <a href="{{ route('student.cart.index') }}" class="btn-secondary">مراجعة السلة</a>
                                @endif
                            </div>
                        </div>
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
