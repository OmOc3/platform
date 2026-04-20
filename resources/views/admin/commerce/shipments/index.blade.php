<x-layouts.admin title="الشحن" heading="الشحن" subheading="متابعة تجهيز شحنات الكتب، حالتها اللوجستية، والطلبات المرتبطة بها.">
    <section class="admin-metric-grid">
        <x-admin.metric-card label="كل الشحنات" :value="$overview['total']" description="إجمالي الشحنات المسجلة." />
        <x-admin.metric-card label="نشطة" :value="$overview['active']" description="شحنات ما زالت تحت التجهيز أو في الطريق." />
        <x-admin.metric-card label="تم التسليم" :value="$overview['delivered']" description="شحنات وصلت إلى الطالب." />
    </section>

    <x-admin.table-shell title="قائمة الشحنات" description="ابحث باسم المستلم أو رقم الطلب أو فلتر بالحالة والمحافظة.">
        <x-slot:filters>
            <form method="GET" class="grid gap-3 md:grid-cols-[1fr_220px_220px_auto]">
                <div>
                    <label class="field-label" for="shipments_search">بحث</label>
                    <input id="shipments_search" type="search" name="search" value="{{ request('search') }}" class="form-input" placeholder="اسم المستلم أو رقم الطلب أو المرجع">
                </div>
                <div>
                    <label class="field-label" for="shipments_status">الحالة</label>
                    <select id="shipments_status" name="status" class="form-select">
                        <option value="">كل الحالات</option>
                        @foreach ($statuses as $status)
                            <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="field-label" for="shipments_governorate">المحافظة</label>
                    <input id="shipments_governorate" type="text" name="governorate" value="{{ request('governorate') }}" class="form-input" placeholder="القاهرة">
                </div>
                <button class="btn-secondary md:self-end">تطبيق الفلاتر</button>
            </form>
        </x-slot:filters>

        <table class="data-table">
            <thead>
                <tr>
                    <th>الطلب</th>
                    <th>المستلم</th>
                    <th>المحافظة / المدينة</th>
                    <th>الرسوم</th>
                    <th>الحالة</th>
                    <th>إجراء</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($shipments as $shipment)
                    <tr>
                        <td class="font-mono text-xs">{{ $shipment->order?->uuid ?? '—' }}</td>
                        <td>
                            <p class="font-semibold">{{ $shipment->recipient_name }}</p>
                            <p class="text-xs text-[var(--color-ink-500)]">{{ $shipment->phone }}</p>
                        </td>
                        <td>{{ $shipment->governorate }} / {{ $shipment->city }}</td>
                        <td>{{ number_format($shipment->shipping_fee_amount) }} {{ $shipment->currency }}</td>
                        <td><x-admin.status-badge :label="$shipment->status->label()" :tone="$shipment->status->tone()" /></td>
                        <td><a href="{{ route('admin.shipments.show', $shipment) }}" class="btn-secondary !px-4 !py-2">التفاصيل</a></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-[var(--color-ink-500)]">لا توجد شحنات مطابقة للفلاتر الحالية.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="px-5 py-4">{{ $shipments->links() }}</div>
    </x-admin.table-shell>
</x-layouts.admin>
