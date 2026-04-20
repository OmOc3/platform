<x-layouts.admin title="الشحنة {{$shipment->id}}" heading="تفاصيل الشحنة" subheading="مراجعة العنوان، الرسوم، حالة الشحنة، وتحديثها وفقًا للمراحل المسموح بها.">
    <section class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_340px]">
        <div class="space-y-6">
            <section class="table-shell">
                <div class="flex flex-col gap-4 px-5 py-5 lg:flex-row lg:items-start lg:justify-between">
                    <div class="space-y-2">
                        <h2 class="text-lg font-bold">العنوان وبيانات المستلم</h2>
                        <p class="text-sm leading-7 text-[var(--color-ink-700)]">رقم الطلب: <span class="font-mono text-xs">{{ $shipment->order?->uuid ?? '—' }}</span></p>
                    </div>
                    <x-admin.status-badge :label="$shipment->status->label()" :tone="$shipment->status->tone()" />
                </div>

                <div class="grid gap-4 border-t border-[var(--color-border-soft)] px-5 py-5 md:grid-cols-2 xl:grid-cols-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-ink-500)]">المستلم</p>
                        <p class="mt-2 font-semibold">{{ $shipment->recipient_name }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-ink-500)]">الهاتف</p>
                        <p class="mt-2 font-semibold">{{ $shipment->phone }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-ink-500)]">المحافظة</p>
                        <p class="mt-2 font-semibold">{{ $shipment->governorate }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-ink-500)]">المدينة</p>
                        <p class="mt-2 font-semibold">{{ $shipment->city }}</p>
                    </div>
                </div>

                <div class="grid gap-4 border-t border-[var(--color-border-soft)] px-5 py-5 md:grid-cols-2">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-ink-500)]">العنوان</p>
                        <p class="mt-2 font-semibold">{{ $shipment->address_line1 }}</p>
                        @if ($shipment->address_line2)
                            <p class="text-sm text-[var(--color-ink-600)]">{{ $shipment->address_line2 }}</p>
                        @endif
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-ink-500)]">الرسوم</p>
                        <p class="mt-2 font-semibold">{{ number_format($shipment->shipping_fee_amount) }} {{ $shipment->currency }}</p>
                    </div>
                </div>
            </section>
        </div>

        <aside class="space-y-6">
            <section class="panel p-5">
                <h2 class="text-lg font-bold">تحديث الحالة</h2>
                @if ($availableTransitions === [])
                    <div class="mt-4 rounded-[1.4rem] bg-[var(--color-panel-muted)] px-4 py-4 text-sm text-[var(--color-ink-700)]">
                        لا توجد انتقالات متاحة لهذه الشحنة الآن.
                    </div>
                @else
                    <form method="POST" action="{{ route('admin.shipments.update', $shipment) }}" class="mt-4 space-y-4">
                        @csrf
                        @method('PUT')
                        <div>
                            <label class="field-label" for="shipment_status">الحالة الجديدة</label>
                            <select id="shipment_status" name="status" class="form-select">
                                @foreach ($availableTransitions as $status)
                                    <option value="{{ $status->value }}">{{ $status->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="field-label" for="carrier_name">اسم الناقل</label>
                            <input id="carrier_name" name="carrier_name" class="form-input" value="{{ old('carrier_name', $shipment->carrier_name) }}">
                        </div>
                        <div>
                            <label class="field-label" for="carrier_reference">مرجع الناقل</label>
                            <input id="carrier_reference" name="carrier_reference" class="form-input" value="{{ old('carrier_reference', $shipment->carrier_reference) }}">
                        </div>
                        <div>
                            <label class="field-label" for="shipment_notes">ملاحظات</label>
                            <textarea id="shipment_notes" name="notes" class="form-textarea" rows="4">{{ old('notes') }}</textarea>
                        </div>
                        <button class="btn-primary w-full justify-center">حفظ التحديث</button>
                    </form>
                @endif
            </section>
        </aside>
    </section>
</x-layouts.admin>
