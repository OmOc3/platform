<x-layouts.admin title="المدفوعات" heading="المدفوعات" subheading="متابعة محاولات الدفع، المراجع الخارجية، والحالات التي تحتاج مراجعة أو ارتجاع.">
    <section class="admin-metric-grid">
        <x-admin.metric-card label="كل العمليات" :value="$overview['total']" description="إجمالي محاولات الدفع المسجلة." />
        <x-admin.metric-card label="تحت الإجراء" :value="$overview['pending']" description="عمليات قيد الانتظار أو تحتاج إجراء إضافي." />
        <x-admin.metric-card label="مدفوعة" :value="$overview['paid']" description="عمليات تم تأكيد سدادها." />
        <x-admin.metric-card label="فاشلة" :value="$overview['failed']" description="عمليات تحتاج إعادة محاولة أو متابعة." />
    </section>

    <x-admin.table-shell title="سجل المدفوعات" description="ابحث برقم الطلب أو مرجع العملية أو اسم الطالب، ثم افتح التفاصيل لمراجعة الـ webhook أو تنفيذ الارتجاع.">
        <x-slot:filters>
            <form method="GET" class="grid gap-3 md:grid-cols-[1fr_220px_180px_auto]">
                <div>
                    <label class="field-label" for="payments_search">بحث</label>
                    <input id="payments_search" type="search" name="search" value="{{ request('search') }}" class="form-input" placeholder="مرجع العملية أو رقم الطلب أو اسم الطالب">
                </div>
                <div>
                    <label class="field-label" for="payments_status">الحالة</label>
                    <select id="payments_status" name="status" class="form-select">
                        <option value="">كل الحالات</option>
                        @foreach ($statuses as $status)
                            <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="field-label" for="payments_provider">المزود</label>
                    <input id="payments_provider" type="text" name="provider" value="{{ request('provider') }}" class="form-input" placeholder="fake">
                </div>
                <button class="btn-secondary md:self-end">تطبيق الفلاتر</button>
            </form>
        </x-slot:filters>

        <table class="data-table">
            <thead>
                <tr>
                    <th>الطلب</th>
                    <th>الطالب</th>
                    <th>المزود</th>
                    <th>المرجع</th>
                    <th>المبلغ</th>
                    <th>الحالة</th>
                    <th>إجراء</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($payments as $payment)
                    <tr>
                        <td class="font-mono text-xs">{{ $payment->order?->uuid ?? '—' }}</td>
                        <td>{{ $payment->order?->student?->name ?? '—' }}</td>
                        <td>{{ strtoupper($payment->provider) }}</td>
                        <td class="font-mono text-xs">{{ $payment->provider_reference ?? '—' }}</td>
                        <td>{{ number_format($payment->amount) }} {{ $payment->currency }}</td>
                        <td><x-admin.status-badge :label="$payment->status->label()" :tone="$payment->status->tone()" /></td>
                        <td><a href="{{ route('admin.payments.show', $payment) }}" class="btn-secondary !px-4 !py-2">التفاصيل</a></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-[var(--color-ink-500)]">لا توجد عمليات مطابقة للفلاتر الحالية.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="px-5 py-4">{{ $payments->links() }}</div>
    </x-admin.table-shell>
</x-layouts.admin>
