<x-layouts.student :title="$book->product?->name_ar" :heading="$book->product?->name_ar" subheading="صفحة تفصيلية للكتاب مع حالة المخزون الحالية، المحافظات المدعومة للشحن، وإمكانية إضافته إلى السلة أو مراجعتها.">
    <section class="grid gap-6 xl:grid-cols-[1.18fr_0.82fr]">
        <article class="space-y-6">
            <section class="panel-tight">
                <div class="grid gap-5 lg:grid-cols-[0.8fr_1.2fr] lg:items-center">
                    <div class="catalog-thumb catalog-thumb--book min-h-[18rem]">
                        @if ($book->product?->thumbnail_url)
                            <img src="{{ $book->product->thumbnail_url }}" alt="{{ $book->product?->name_ar }}">
                        @else
                            <div class="catalog-thumb__fallback">
                                <span>{{ $book->cover_badge ?: 'كتاب تعليمي' }}</span>
                                <strong>{{ $book->page_count ?: '—' }} صفحة</strong>
                            </div>
                        @endif
                    </div>

                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <x-admin.status-badge :label="$book->availability_status->label()" :tone="$book->availability_status->tone()" />
                            @if ($book->cover_badge)
                                <x-admin.status-badge :label="$book->cover_badge" />
                            @endif
                            @if ($inCart)
                                <x-admin.status-badge label="تمت الإضافة" />
                            @endif
                        </div>

                        <p class="mt-5 text-base leading-9 text-[var(--color-ink-700)]">{{ $book->product?->description }}</p>

                        <dl class="mt-6 grid gap-3 sm:grid-cols-3">
                            <div class="rounded-[1.4rem] bg-[var(--color-brand-50)] p-4">
                                <dt class="text-xs text-[var(--color-ink-500)]">المؤلف</dt>
                                <dd class="mt-2 font-semibold">{{ $book->author_name ?: '—' }}</dd>
                            </div>
                            <div class="rounded-[1.4rem] bg-[var(--color-brand-50)] p-4">
                                <dt class="text-xs text-[var(--color-ink-500)]">الصفحات</dt>
                                <dd class="mt-2 font-semibold">{{ $book->page_count ?: '—' }}</dd>
                            </div>
                            <div class="rounded-[1.4rem] bg-[var(--color-brand-50)] p-4">
                                <dt class="text-xs text-[var(--color-ink-500)]">السعر</dt>
                                <dd class="mt-2 font-semibold">{{ number_format($book->product?->price_amount ?? 0) }} {{ $book->product?->currency }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </section>

            <section class="panel-tight">
                <p class="text-sm font-semibold text-[var(--color-brand-700)]">الشحن والتغطية</p>
                <div class="mt-5 grid gap-4 lg:grid-cols-[0.9fr_1.1fr]">
                    <div class="rounded-[1.8rem] bg-[var(--color-panel-muted)] p-5">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-ink-500)]">المخزون الحالي</p>
                        <p class="mt-3 text-3xl font-bold text-[var(--color-brand-700)]">{{ $book->stock_quantity }}</p>
                        <p class="mt-3 text-sm leading-8 text-[var(--color-ink-700)]">
                            @if ($book->availability_status->value === 'in_stock')
                                متاح للشحن الآن ويمكن إضافته مباشرة إلى السلة.
                            @elseif ($book->availability_status->value === 'pre_order')
                                متاح للحجز المسبق وسيظهر ضمن طلبات الكتب حتى لحظة التأكيد.
                            @else
                                نفد المخزون الحالي وسيظهر كغير متاح للشراء.
                            @endif
                        </p>
                    </div>

                    <div class="rounded-[1.8rem] bg-[var(--color-panel-muted)] p-5">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-ink-500)]">المحافظات المدعومة</p>
                        <div class="mt-4 flex flex-wrap gap-2">
                            @forelse ($supportedGovernorates as $governorate)
                                <span class="status-pill bg-white text-[var(--color-brand-700)]">{{ $governorate }}</span>
                            @empty
                                <span class="text-sm text-[var(--color-ink-700)]">لم يتم تقييد المحافظات لهذا الكتاب بعد.</span>
                            @endforelse
                        </div>
                    </div>
                </div>
            </section>
        </article>

        <aside class="space-y-6">
            <section class="panel-tight">
                <p class="text-sm font-semibold text-[var(--color-brand-700)]">الطلب والشحن</p>
                <h2 class="mt-3 text-2xl font-bold">{{ $book->product?->name_ar }}</h2>
                <p class="mt-4 text-sm leading-8 text-[var(--color-ink-700)]">
                    السعر الحالي {{ number_format($book->product?->price_amount ?? 0) }} {{ $book->product?->currency }}.
                    حالة الكتاب الآن: {{ $book->availability_status->label() }}.
                </p>

                <div class="mt-6 flex flex-col gap-3">
                    @if ($book->availability_status->value !== 'sold_out' && $book->product && ! $inCart)
                        <form method="POST" action="{{ route('student.cart.store') }}">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $book->product->id }}">
                            <button class="btn-primary w-full">أضف الكتاب إلى السلة</button>
                        </form>
                    @elseif ($inCart)
                        <a href="{{ route('student.cart.index') }}" class="btn-primary w-full">الكتاب موجود في السلة</a>
                    @else
                        <button type="button" class="btn-secondary !cursor-default !opacity-70">نفد المخزون الحالي</button>
                    @endif

                    <a href="{{ route('student.books.index') }}" class="btn-secondary">العودة إلى الكتب</a>
                </div>
            </section>

            <section class="panel-tight">
                <p class="text-sm font-semibold text-[var(--color-brand-700)]">تنبيه قبل الطلب</p>
                <p class="mt-4 text-sm leading-8 text-[var(--color-ink-700)]">
                    راجع المحافظة ورقم الهاتف من صفحة السلة أو صفحة الإعدادات قبل تجهيز طلب الكتب، لأن النظام الحالي يعتمد على بيانات الطالب المسجلة للتحقق اللوجستي.
                </p>
            </section>
        </aside>
    </section>
</x-layouts.student>
