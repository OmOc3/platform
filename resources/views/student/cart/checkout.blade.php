<x-layouts.student title="تجهيز الطلب" heading="تجهيز الطلبات" subheading="أنشئ مسودات منفصلة للعناصر الرقمية وطلبات الكتب قبل ربط بوابة الدفع لاحقًا.">
    <section class="space-y-6">
        <section class="panel-tight flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-sm font-semibold text-[var(--color-brand-700)]">إجمالي السلة</p>
                <p class="mt-2 text-2xl font-bold">{{ number_format($grandTotal) }} {{ $cart->currency }}</p>
            </div>
            <form method="POST" action="{{ route('student.checkout.prepare') }}">
                @csrf
                <button class="btn-primary">إنشاء مسودة الطلبات</button>
            </form>
        </section>

        <div class="grid gap-6 xl:grid-cols-2">
            <section class="panel-tight">
                <p class="text-sm font-semibold text-[var(--color-brand-700)]">مسودة الطلب الرقمي</p>
                @if (! $digitalOrder)
                    <div class="mt-4">
                        <x-student.empty-state title="لا توجد مسودة رقمية بعد" description="أضف محاضرات أو باقات ثم اضغط تجهيز الطلبات." />
                    </div>
                @else
                    <div class="mt-4 space-y-3">
                        @foreach ($digitalOrder->items as $item)
                            <div class="rounded-[1.4rem] bg-[var(--color-brand-50)] px-4 py-4">
                                <div class="flex items-center justify-between gap-4">
                                    <div>
                                        <p class="font-semibold">{{ $item->product_name_snapshot }}</p>
                                        <p class="mt-2 text-xs text-[var(--color-ink-500)]">{{ $item->product_kind->value }}</p>
                                    </div>
                                    <span class="font-semibold">{{ number_format($item->total_price_amount) }} {{ $digitalOrder->currency }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>

            <section class="panel-tight">
                <p class="text-sm font-semibold text-[var(--color-brand-700)]">مسودة طلب الكتب</p>
                @if (! $bookOrder)
                    <div class="mt-4">
                        <x-student.empty-state title="لا توجد مسودة كتب بعد" description="أضف كتبًا إلى السلة ثم جهّز الطلبات." />
                    </div>
                @else
                    <div class="mt-4 space-y-3">
                        @foreach ($bookOrder->items as $item)
                            <div class="rounded-[1.4rem] bg-[var(--color-brand-50)] px-4 py-4">
                                <div class="flex items-center justify-between gap-4">
                                    <div>
                                        <p class="font-semibold">{{ $item->product_name_snapshot }}</p>
                                        <p class="mt-2 text-xs text-[var(--color-ink-500)]">الكمية: {{ $item->quantity }}</p>
                                    </div>
                                    <span class="font-semibold">{{ number_format($item->total_price_amount) }} {{ $bookOrder->currency }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>
        </div>
    </section>
</x-layouts.student>
