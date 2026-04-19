<x-layouts.admin
    :title="'الطلب '.$order->uuid"
    heading="تفاصيل الطلب"
    subheading="مراجعة عناصر الطلب، تتبع الحالة الحالية، وتنفيذ انتقالات آمنة مع الحفاظ على سجل الاستحقاقات."
>
    <section class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_360px]">
        <div class="space-y-6">
            <section class="table-shell">
                <div class="flex flex-col gap-4 px-5 py-5 lg:flex-row lg:items-start lg:justify-between">
                    <div class="space-y-2">
                        <h2 class="text-lg font-bold">بيانات الطلب</h2>
                        <p class="text-sm leading-7 text-[var(--color-ink-700)]">رقم الطلب: <span class="font-mono text-xs">{{ $order->uuid }}</span></p>
                    </div>
                    <x-admin.status-badge :label="$order->status->label()" :tone="$order->status->tone()" />
                </div>

                <div class="grid gap-4 border-t border-[color-mix(in_oklch,var(--color-brand-100)_80%,white)] px-5 py-5 md:grid-cols-2 xl:grid-cols-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-ink-500)]">الطالب</p>
                        <p class="mt-2 font-semibold">{{ $order->student?->name ?? '—' }}</p>
                        <p class="text-sm text-[var(--color-ink-600)]">{{ $order->student?->email ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-ink-500)]">النوع</p>
                        <p class="mt-2 font-semibold">{{ $order->kind->label() }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-ink-500)]">الإجمالي</p>
                        <p class="mt-2 font-semibold">{{ number_format($order->total_amount) }} {{ $order->currency }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-ink-500)]">تاريخ الوضع</p>
                        <p class="mt-2 font-semibold">{{ optional($order->placed_at ?? $order->created_at)->format('Y-m-d H:i') }}</p>
                    </div>
                </div>
            </section>

            <x-admin.table-shell title="عناصر الطلب" description="يعرض كل عنصر مصدر الشراء وقيمة السطر وما إذا تم توليد استحقاق رقمي له.">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>العنصر</th>
                            <th>النوع</th>
                            <th>الكمية</th>
                            <th>السعر</th>
                            <th>الاستحقاق</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($order->items as $item)
                            <tr>
                                <td class="font-semibold">{{ $item->product_name_snapshot }}</td>
                                <td>{{ $item->product_kind?->label() ?? '—' }}</td>
                                <td>{{ $item->quantity }}</td>
                                <td>{{ number_format($item->total_price_amount) }} {{ $order->currency }}</td>
                                <td>
                                    @if ($item->entitlement)
                                        <x-admin.status-badge label="تم التفعيل" tone="success" />
                                    @elseif ($order->kind === \App\Shared\Enums\OrderKind::Book)
                                        <x-admin.status-badge label="غير مطلوب" tone="neutral" />
                                    @else
                                        <x-admin.status-badge label="لم يُفعّل بعد" tone="warning" />
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </x-admin.table-shell>

            @if ($canViewEntitlements)
                <x-admin.table-shell title="الاستحقاقات المرتبطة" description="تظهر هنا الاستحقاقات المولدة من هذا الطلب بعد التفعيل.">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>العنصر</th>
                                <th>المصدر</th>
                                <th>الحالة</th>
                                <th>تاريخ التفعيل</th>
                                <th>الانتهاء</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($order->items->pluck('entitlement')->filter() as $entitlement)
                                <tr>
                                    <td class="font-semibold">{{ $entitlement->item_name_snapshot }}</td>
                                    <td>{{ $entitlement->source->label() }}</td>
                                    <td><x-admin.status-badge :label="$entitlement->status" tone="success" /></td>
                                    <td>{{ optional($entitlement->granted_at)->format('Y-m-d H:i') ?: '—' }}</td>
                                    <td>{{ optional($entitlement->ends_at)->format('Y-m-d') ?: 'مفتوح' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-[var(--color-ink-500)]">لا توجد استحقاقات مرتبطة بهذا الطلب بعد.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </x-admin.table-shell>
            @endif
        </div>

        <aside class="space-y-6">
            <section class="panel p-5">
                <h2 class="text-lg font-bold">الإجراءات المتاحة</h2>
                <p class="mt-2 text-sm leading-7 text-[var(--color-ink-700)]">لا تظهر هنا إلا الانتقالات المسموح بها من الحالة الحالية للطلب.</p>

                <div class="mt-5 space-y-3">
                    @forelse ($availableTransitions as $status)
                        <form method="POST" action="{{ route('admin.orders.transition', $order) }}">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="status" value="{{ $status->value }}">
                            <button type="submit" class="{{ $status === \App\Shared\Enums\OrderStatus::Cancelled ? 'btn-danger' : 'btn-primary' }} w-full justify-center">
                                {{ $status === \App\Shared\Enums\OrderStatus::Fulfilled ? 'تفعيل الطلب' : 'نقل إلى: '.$status->label() }}
                            </button>
                        </form>
                    @empty
                        <div class="rounded-3xl bg-[var(--color-brand-50)] px-4 py-4 text-sm leading-7 text-[var(--color-ink-700)]">
                            لا توجد انتقالات متاحة لهذه الحالة حاليًا.
                        </div>
                    @endforelse
                </div>
            </section>

            <section class="panel p-5">
                <h2 class="text-lg font-bold">ملاحظات السلوك</h2>
                <ul class="mt-4 space-y-3 text-sm leading-7 text-[var(--color-ink-700)]">
                    <li>الطلبات الرقمية تُنشئ استحقاقات عند الانتقال إلى حالة "مفعّل".</li>
                    <li>طلبات الكتب لا تُنشئ استحقاقات رقمية حتى بعد التفعيل.</li>
                    <li>إعادة إرسال نفس التفعيل لا تضاعف الاستحقاقات المرتبطة بالعناصر.</li>
                </ul>
            </section>
        </aside>
    </section>
</x-layouts.admin>
