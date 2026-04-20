<x-layouts.student title="مدفوعات المحاضرات" heading="مدفوعات المحاضرات والوصول الرقمي" subheading="سجل التفعيلات الرقمية والمدفوعات والمنح الإدارية المرتبطة بحسابك.">
    <section class="space-y-6">
        <x-student.account-nav current="payments" />

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <x-student.summary-card label="إجمالي السجلات" :value="$summary['count']" description="كل عناصر الوصول المسجلة على الحساب" />
            <x-student.summary-card label="مفعّل الآن" :value="$summary['active_count']" description="عناصر نشطة متاحة للاستخدام" />
            <x-student.summary-card label="عمليات مدفوعة" :value="$summary['paid_count']" description="تفعيلات نتجت عن شراء مباشر" />
            <x-student.summary-card label="إجمالي المدفوع" :value="number_format($summary['spent_total']).' ج'" description="قيمة المدفوعات الرقمية المسجلة" />
        </section>

        <section class="table-shell">
            <div class="flex flex-col gap-3 px-5 py-5 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="text-sm font-semibold text-[var(--color-brand-700)]">سجل الاستحقاقات</p>
                    <h2 class="mt-2 text-xl font-bold">مدفوعات المحاضرات والباقات</h2>
                </div>
                <a href="{{ route('student.lectures.index') }}" class="btn-secondary">الذهاب إلى المحاضرات</a>
            </div>

            @if ($entitlements->isEmpty())
                <div class="px-5 pb-5">
                    <x-student.empty-state title="لا توجد سجلات بعد" description="سيظهر هنا أي شراء رقمي أو منحة إدارية أو تفعيل ناتج عن باقة." />
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>العنصر</th>
                                <th>المصدر</th>
                                <th>التاريخ</th>
                                <th>فترة التفعيل</th>
                                <th>القيمة</th>
                                <th>الحالة</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($entitlements as $row)
                                <tr>
                                    <td class="font-semibold">
                                        <div class="flex flex-col gap-1">
                                            <span>{{ $row->item_name_snapshot }}</span>
                                            @if ($row->orderItem?->order?->uuid)
                                                <span class="text-xs text-[var(--color-ink-500)]">{{ $row->orderItem->order->uuid }}</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>{{ $row->source->label() }}</td>
                                    <td>
                                        <div class="flex flex-col gap-1">
                                            <span>{{ optional($row->granted_at)->format('Y-m-d') ?: '—' }}</span>
                                            <span class="text-xs text-[var(--color-ink-500)]">{{ optional($row->granted_at)->format('H:i') ?: '—' }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        @if ($row->starts_at || $row->ends_at)
                                            <div class="flex flex-col gap-1">
                                                <span>من {{ optional($row->starts_at)->format('Y-m-d') ?: 'الآن' }}</span>
                                                <span class="text-xs text-[var(--color-ink-500)]">إلى {{ optional($row->ends_at)->format('Y-m-d') ?: 'مفتوح' }}</span>
                                            </div>
                                        @else
                                            <span>بدون حد زمني</span>
                                        @endif
                                    </td>
                                    <td>{{ number_format($row->price_amount) }} {{ $row->currency }}</td>
                                    <td><x-admin.status-badge :label="$row->status === 'active' ? 'مفعّل' : $row->status" :tone="$row->status === 'active' ? 'success' : 'warning'" /></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="px-5 py-4">
                    {{ $entitlements->links() }}
                </div>
            @endif
        </section>
    </section>
</x-layouts.student>
