<x-layouts.student title="المدفوعات الرقمية" heading="مدفوعات المحاضرات والوصول الرقمي" subheading="سجل كل استحقاق رقمي تم منحه أو شراؤه أو إضافته من باقة أو إدارة.">
    <section class="table-shell">
        <div class="px-5 py-5">
            <h2 class="text-lg font-bold">سجل الاستحقاقات</h2>
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
                            <th>التاريخ</th>
                            <th>السعر</th>
                            <th>الحالة</th>
                            <th>المصدر</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($entitlements as $row)
                            <tr>
                                <td class="font-semibold">{{ $row->item_name_snapshot }}</td>
                                <td>{{ optional($row->granted_at)->format('Y-m-d') ?: '—' }}</td>
                                <td>{{ number_format($row->price_amount) }} {{ $row->currency }}</td>
                                <td><x-admin.status-badge :label="$row->status" tone="success" /></td>
                                <td>{{ $row->source->value }}</td>
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
</x-layouts.student>
