<x-layouts.admin title="المدفوعة {{$payment->id}}" heading="تفاصيل المدفوعة" subheading="مراجعة حالة المدفوعة، مرجع المزود، الـ webhook receipts، وإجراء الارتجاع عند الحاجة.">
    <section class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_340px]">
        <div class="space-y-6">
            <section class="table-shell">
                <div class="flex flex-col gap-4 px-5 py-5 lg:flex-row lg:items-start lg:justify-between">
                    <div class="space-y-2">
                        <h2 class="text-lg font-bold">بيانات العملية</h2>
                        <p class="text-sm leading-7 text-[var(--color-ink-700)]">الطلب: <span class="font-mono text-xs">{{ $payment->order?->uuid ?? '—' }}</span></p>
                    </div>
                    <x-admin.status-badge :label="$payment->status->label()" :tone="$payment->status->tone()" />
                </div>

                <div class="grid gap-4 border-t border-[var(--color-border-soft)] px-5 py-5 md:grid-cols-2 xl:grid-cols-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-ink-500)]">الطالب</p>
                        <p class="mt-2 font-semibold">{{ $payment->order?->student?->name ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-ink-500)]">المزود</p>
                        <p class="mt-2 font-semibold">{{ strtoupper($payment->provider) }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-ink-500)]">المبلغ</p>
                        <p class="mt-2 font-semibold">{{ number_format($payment->amount) }} {{ $payment->currency }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-ink-500)]">المحاولة</p>
                        <p class="mt-2 font-semibold">{{ $payment->attempt_number }}</p>
                    </div>
                </div>

                <div class="grid gap-4 border-t border-[var(--color-border-soft)] px-5 py-5 md:grid-cols-2">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-ink-500)]">مرجع المزود</p>
                        <p class="mt-2 font-mono text-xs">{{ $payment->provider_reference ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-ink-500)]">مرجع المعاملة</p>
                        <p class="mt-2 font-mono text-xs">{{ $payment->provider_transaction_reference ?? '—' }}</p>
                    </div>
                </div>
            </section>

            <x-admin.table-shell title="عناصر الطلب" description="المنتجات التي تم ربط هذه العملية بها.">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>العنصر</th>
                            <th>النوع</th>
                            <th>الإجمالي</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($payment->order->items as $item)
                            <tr>
                                <td class="font-semibold">{{ $item->product_name_snapshot }}</td>
                                <td>{{ $item->product_kind?->label() ?? '—' }}</td>
                                <td>{{ number_format($item->total_price_amount) }} {{ $payment->currency }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </x-admin.table-shell>

            <x-admin.table-shell title="سجل الـ Webhooks" description="آخر الأحداث التي عولجت على هذه المدفوعة.">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>الحدث</th>
                            <th>الحالة</th>
                            <th>وقت المعالجة</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($payment->webhookReceipts as $receipt)
                            <tr>
                                <td class="font-mono text-xs">{{ $receipt->event_key }}</td>
                                <td>{{ $receipt->status ?: '—' }}</td>
                                <td>{{ optional($receipt->processed_at)->format('Y-m-d H:i') ?: '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-[var(--color-ink-500)]">لا توجد webhooks مسجلة لهذه العملية بعد.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </x-admin.table-shell>
        </div>

        <aside class="space-y-6">
            <section class="panel p-5">
                <h2 class="text-lg font-bold">إجراءات خلفية</h2>
                <p class="mt-2 text-sm leading-7 text-[var(--color-ink-700)]">يمكن تنفيذ الارتجاع فقط بعد التأكد من أن العملية سددت بالفعل وأن الطلب المرتبط بها يجب إغلاقه ماليًا.</p>

                @can('update', $payment)
                    @if (in_array($payment->status, [\App\Shared\Enums\PaymentStatus::Paid, \App\Shared\Enums\PaymentStatus::Refunded], true))
                        <form method="POST" action="{{ route('admin.payments.refund', $payment) }}" class="mt-5 space-y-3">
                            @csrf
                            @method('PUT')
                            <div>
                                <label class="field-label" for="refund_reason">سبب الارتجاع</label>
                                <textarea id="refund_reason" name="reason" class="form-textarea" rows="4">{{ old('reason') }}</textarea>
                            </div>
                            <button class="btn-danger w-full justify-center">تسجيل الارتجاع</button>
                        </form>
                    @else
                        <div class="mt-5 rounded-[1.4rem] bg-[var(--color-panel-muted)] px-4 py-4 text-sm text-[var(--color-ink-700)]">
                            لا يمكن تنفيذ ارتجاع قبل أن تصبح العملية مدفوعة.
                        </div>
                    @endif
                @endcan
            </section>
        </aside>
    </section>
</x-layouts.admin>
