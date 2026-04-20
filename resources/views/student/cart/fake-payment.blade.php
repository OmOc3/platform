<x-layouts.student title="بوابة الدفع التجريبية" heading="بوابة الدفع التجريبية" subheading="هذه شاشة محاكاة لمزود الدفع حتى نختبر دورة الدفع، الـ webhooks، وحالة الطلب دون ربط مزود خارجي فعلي حتى الآن.">
    <section class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_340px]">
        <section class="panel-tight">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-sm font-semibold text-[var(--color-brand-700)]">تفاصيل العملية</p>
                    <h2 class="mt-2 text-2xl font-bold">{{ $payment->order->uuid }}</h2>
                    <p class="mt-3 text-sm leading-8 text-[var(--color-ink-700)]">
                        استخدم هذه الصفحة لتجربة السيناريوهات المختلفة: نجاح السداد، فشله، أو إلغاؤه. المعالجة الخلفية تمر من نفس خط الـ webhook والتسويات المستخدمين في النظام.
                    </p>
                </div>

                <x-admin.status-badge :label="$payment->status->label()" :tone="$payment->status->tone()" />
            </div>

            <div class="mt-6 grid gap-4 md:grid-cols-3">
                <div class="surface-card rounded-[1.6rem] p-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-ink-500)]">المبلغ</p>
                    <p class="mt-2 text-xl font-bold">{{ number_format($payment->amount) }} {{ $payment->currency }}</p>
                </div>
                <div class="surface-card rounded-[1.6rem] p-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-ink-500)]">رقم المرجع</p>
                    <p class="mt-2 font-mono text-xs">{{ $payment->provider_reference }}</p>
                </div>
                <div class="surface-card rounded-[1.6rem] p-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-ink-500)]">انتهاء الصلاحية</p>
                    <p class="mt-2 font-semibold">{{ optional($payment->expires_at)->format('Y-m-d H:i') ?: '—' }}</p>
                </div>
            </div>

            <div class="mt-6 space-y-3">
                @foreach ($payment->order->items as $item)
                    <article class="surface-card rounded-[1.6rem] p-4">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="font-semibold">{{ $item->product_name_snapshot }}</p>
                                <p class="mt-2 text-xs text-[var(--color-ink-500)]">{{ $item->product_kind->label() }}</p>
                            </div>
                            <span class="font-semibold">{{ number_format($item->total_price_amount) }} {{ $payment->currency }}</span>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>

        <aside class="space-y-6">
            <section class="panel-tight">
                <p class="text-sm font-semibold text-[var(--color-brand-700)]">اختر النتيجة</p>
                <div class="mt-5 space-y-3">
                    @foreach ($statuses as $status)
                        <form method="POST" action="{{ route('student.order-payments.fake.complete', $payment) }}">
                            @csrf
                            <input type="hidden" name="status" value="{{ $status->value }}">
                            <button class="{{ $status === \App\Shared\Enums\PaymentStatus::Paid ? 'btn-primary' : 'btn-secondary' }} w-full justify-center">
                                {{ $status->label() }}
                            </button>
                        </form>
                    @endforeach
                </div>
            </section>

            @if ($payment->order->shipment)
                <section class="panel-tight">
                    <p class="text-sm font-semibold text-[var(--color-brand-700)]">ملخص الشحن المتوقع</p>
                    <div class="mt-4 space-y-3 text-sm">
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-[var(--color-ink-500)]">المستلم</span>
                            <strong>{{ $payment->order->shipment->recipient_name }}</strong>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-[var(--color-ink-500)]">المحافظة</span>
                            <strong>{{ $payment->order->shipment->governorate }}</strong>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-[var(--color-ink-500)]">رسوم الشحن</span>
                            <strong>{{ number_format($payment->order->shipment->shipping_fee_amount) }} {{ $payment->order->shipment->currency }}</strong>
                        </div>
                    </div>
                </section>
            @endif
        </aside>
    </section>
</x-layouts.student>
