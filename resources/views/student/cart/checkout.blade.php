<x-layouts.student title="إتمام الطلب" heading="إتمام الطلب" subheading="مراجعة مسودات الطلبات الرقمية وطلبات الكتب مع ملخص التسعير وبيانات الاستلام الحالية قبل الانتقال للدفع أو التنفيذ.">
    <section class="space-y-6">
        <section class="panel-tight">
            <div class="grid gap-4 lg:grid-cols-[1.05fr_0.95fr] lg:items-center">
                <div>
                    <p class="text-sm font-semibold text-[var(--color-brand-700)]">ملخص السلة</p>
                    <h2 class="mt-2 text-2xl font-bold lg:text-3xl">راجع المسودات قبل اعتماد الطلب.</h2>
                    <p class="mt-3 text-sm leading-8 text-[var(--color-ink-700)]">
                        النظام الحالي يفصل تلقائيًا بين العناصر الرقمية وطلبات الكتب. عند الضغط على تجهيز الطلب، يتم إنشاء أو تحديث مسودة لكل نوع على حدة.
                    </p>
                </div>

                <div class="grid gap-3 sm:grid-cols-3">
                    <div class="portal-shell-meta">
                        <span class="portal-shell-meta__label">الإجمالي الرقمي</span>
                        <strong class="portal-shell-meta__value">{{ number_format($digitalTotal) }} {{ $cart->currency }}</strong>
                    </div>
                    <div class="portal-shell-meta">
                        <span class="portal-shell-meta__label">إجمالي الكتب</span>
                        <strong class="portal-shell-meta__value">{{ number_format($bookTotal) }} {{ $cart->currency }}</strong>
                    </div>
                    <div class="portal-shell-meta">
                        <span class="portal-shell-meta__label">الإجمالي النهائي</span>
                        <strong class="portal-shell-meta__value">{{ number_format($finalTotal) }} {{ $cart->currency }}</strong>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex flex-wrap gap-3">
                <form method="POST" action="{{ route('student.checkout.prepare') }}">
                    @csrf
                    <button class="btn-primary">إنشاء / تحديث مسودات الطلب</button>
                </form>
                <a href="{{ route('student.cart.index') }}" class="btn-secondary">العودة إلى السلة</a>
            </div>
        </section>

        <section class="grid gap-6 xl:grid-cols-[1.1fr_0.9fr]">
            <div class="space-y-6">
                <section class="panel-tight">
                    <p class="text-sm font-semibold text-[var(--color-brand-700)]">مسودة الطلب الرقمي</p>
                    @if (! $digitalOrder)
                        <div class="mt-4">
                            <x-student.empty-state title="لا توجد مسودة رقمية بعد" description="أضف محاضرات أو باقات ثم اضغط تجهيز الطلب لإنشاء مسودة الطلب الرقمي." />
                        </div>
                    @else
                        <div class="mt-5 space-y-3">
                            @foreach ($digitalOrder->items as $item)
                                <article class="surface-card rounded-[1.8rem] p-4">
                                    <div class="flex items-center justify-between gap-4">
                                        <div>
                                            <p class="font-semibold">{{ $item->product_name_snapshot }}</p>
                                            <p class="mt-2 text-xs text-[var(--color-ink-500)]">{{ $item->product_kind->label() }}</p>
                                        </div>
                                        <span class="font-semibold">{{ number_format($item->total_price_amount) }} {{ $digitalOrder->currency }}</span>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    @endif
                </section>

                <section class="panel-tight">
                    <p class="text-sm font-semibold text-[var(--color-brand-700)]">مسودة طلب الكتب</p>
                    @if (! $bookOrder)
                        <div class="mt-4">
                            <x-student.empty-state title="لا توجد مسودة كتب بعد" description="أضف كتبًا إلى السلة ثم جهّز الطلبات لإنشاء مسودة الشحن." />
                        </div>
                    @else
                        <div class="mt-5 space-y-3">
                            @foreach ($bookOrder->items as $item)
                                <article class="surface-card rounded-[1.8rem] p-4">
                                    <div class="flex items-center justify-between gap-4">
                                        <div>
                                            <p class="font-semibold">{{ $item->product_name_snapshot }}</p>
                                            <p class="mt-2 text-xs text-[var(--color-ink-500)]">الكمية: {{ $item->quantity }}</p>
                                        </div>
                                        <span class="font-semibold">{{ number_format($item->total_price_amount) }} {{ $bookOrder->currency }}</span>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    @endif
                </section>
            </div>

            <aside class="space-y-6">
                <section class="panel-tight">
                    <p class="text-sm font-semibold text-[var(--color-brand-700)]">ملخص الفاتورة</p>
                    <div class="mt-5 space-y-4 text-sm">
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-[var(--color-ink-500)]">الإجمالي الرقمي</span>
                            <strong>{{ number_format($digitalTotal) }} {{ $cart->currency }}</strong>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-[var(--color-ink-500)]">إجمالي الكتب</span>
                            <strong>{{ number_format($bookTotal) }} {{ $cart->currency }}</strong>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-[var(--color-ink-500)]">رسوم الشحن</span>
                            <strong>{{ $shipping['fee_amount'] > 0 ? number_format($shipping['fee_amount']).' '.$cart->currency : $shipping['fee_label'] }}</strong>
                        </div>
                        <div class="border-t border-[var(--color-border-soft)] pt-4">
                            <div class="flex items-center justify-between gap-3">
                                <span class="font-semibold">الإجمالي النهائي</span>
                                <strong class="text-xl text-[var(--color-brand-700)]">{{ number_format($finalTotal) }} {{ $cart->currency }}</strong>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="panel-tight">
                    <p class="text-sm font-semibold text-[var(--color-brand-700)]">بيانات الاستلام الحالية</p>
                    <div class="mt-5 space-y-3 text-sm">
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-[var(--color-ink-500)]">الاسم</span>
                            <strong>{{ $student->name }}</strong>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-[var(--color-ink-500)]">الهاتف</span>
                            <strong>{{ $student->phone ?: '—' }}</strong>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-[var(--color-ink-500)]">المحافظة</span>
                            <strong>{{ $student->governorate ?: '—' }}</strong>
                        </div>
                        <div class="rounded-[1.4rem] bg-[var(--color-panel-muted)] p-4">
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-ink-500)]">ملاحظات الاستلام</p>
                            <p class="mt-2 leading-8 text-[var(--color-ink-700)]">{{ $student->notes ?: 'لا توجد ملاحظات استلام محفوظة حتى الآن.' }}</p>
                        </div>
                    </div>

                    <a href="{{ route('student.cart.index') }}" class="btn-secondary mt-5 w-full">تعديل بيانات الاستلام</a>
                </section>
            </aside>
        </section>
    </section>
</x-layouts.student>
