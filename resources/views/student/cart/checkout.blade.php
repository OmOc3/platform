<x-layouts.student title="إتمام الطلب" heading="إتمام الطلب" subheading="مراجعة مسودات الطلبات الرقمية وطلبات الكتب مع بدء الدفع وتتبع حالة كل طلب قبل التنفيذ النهائي.">
    @php
        $digitalPayment = $digitalOrder?->payments?->first();
        $bookPayment = $bookOrder?->payments?->first();
        $bookShipping = (array) data_get($bookOrder?->meta, 'shipping_address', []);
    @endphp

    <section class="space-y-6">
        <section class="panel-tight">
            <div class="grid gap-4 lg:grid-cols-[1.05fr_0.95fr] lg:items-center">
                <div>
                    <p class="text-sm font-semibold text-[var(--color-brand-700)]">ملخص السلة</p>
                    <h2 class="mt-2 text-2xl font-bold lg:text-3xl">راجع الطلبات ثم ابدأ الدفع من نفس الصفحة.</h2>
                    <p class="mt-3 text-sm leading-8 text-[var(--color-ink-700)]">
                        يظل النظام يفصل تلقائيًا بين العناصر الرقمية وطلبات الكتب. لكل نوع طلب دورة مستقلة في الدفع والتنفيذ حتى تظل المعالجة واضحة وآمنة.
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
                        <span class="portal-shell-meta__label">الإجمالي المتوقع</span>
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
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-sm font-semibold text-[var(--color-brand-700)]">الطلب الرقمي</p>
                            <h3 class="mt-2 text-xl font-bold">محاضرات وباقات</h3>
                        </div>

                        @if ($digitalOrder)
                            <x-admin.status-badge :label="$digitalOrder->status->labelFor($digitalOrder->kind)" :tone="$digitalOrder->status->tone()" />
                        @endif
                    </div>

                    @if (! $digitalOrder)
                        <div class="mt-4">
                            <x-student.empty-state title="لا توجد مسودة رقمية بعد" description="أضف محاضرات أو باقات ثم جهّز الطلب لإنشاء مسودة رقمية قابلة للدفع." />
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

                        <div class="mt-5 grid gap-4 rounded-[1.6rem] bg-[var(--color-panel-muted)] p-4 md:grid-cols-3">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-ink-500)]">رقم الطلب</p>
                                <p class="mt-2 font-mono text-xs">{{ $digitalOrder->uuid }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-ink-500)]">حالة الدفع</p>
                                @if ($digitalPayment)
                                    <div class="mt-2"><x-admin.status-badge :label="$digitalPayment->status->label()" :tone="$digitalPayment->status->tone()" /></div>
                                @else
                                    <p class="mt-2 text-sm text-[var(--color-ink-600)]">لم تبدأ العملية بعد</p>
                                @endif
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-ink-500)]">الإجمالي</p>
                                <p class="mt-2 font-semibold">{{ number_format($digitalOrder->total_amount) }} {{ $digitalOrder->currency }}</p>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('student.checkout.orders.payments.store', $digitalOrder) }}" class="mt-5">
                            @csrf
                            <input type="hidden" name="provider" value="fake">
                            <button class="btn-primary w-full justify-center">
                                {{ $digitalPayment ? 'استكمل الدفع التجريبي' : 'ابدأ الدفع التجريبي' }}
                            </button>
                        </form>
                    @endif
                </section>

                <section class="panel-tight">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-sm font-semibold text-[var(--color-brand-700)]">طلب الكتب</p>
                            <h3 class="mt-2 text-xl font-bold">الشحن والتجهيز</h3>
                        </div>

                        @if ($bookOrder)
                            <x-admin.status-badge :label="$bookOrder->status->labelFor($bookOrder->kind)" :tone="$bookOrder->status->tone()" />
                        @endif
                    </div>

                    @if (! $bookOrder)
                        <div class="mt-4">
                            <x-student.empty-state title="لا توجد مسودة كتب بعد" description="أضف كتبًا إلى السلة ثم جهّز الطلب لإنشاء مسودة قابلة للشحن والدفع." />
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

                        <div class="mt-5 grid gap-4 rounded-[1.6rem] bg-[var(--color-panel-muted)] p-4 md:grid-cols-3">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-ink-500)]">رقم الطلب</p>
                                <p class="mt-2 font-mono text-xs">{{ $bookOrder->uuid }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-ink-500)]">حالة الدفع</p>
                                @if ($bookPayment)
                                    <div class="mt-2"><x-admin.status-badge :label="$bookPayment->status->label()" :tone="$bookPayment->status->tone()" /></div>
                                @else
                                    <p class="mt-2 text-sm text-[var(--color-ink-600)]">لم تبدأ العملية بعد</p>
                                @endif
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-ink-500)]">الإجمالي الحالي</p>
                                <p class="mt-2 font-semibold">{{ number_format($bookOrder->total_amount) }} {{ $bookOrder->currency }}</p>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('student.checkout.orders.payments.store', $bookOrder) }}" class="mt-5 space-y-4">
                            @csrf
                            <input type="hidden" name="provider" value="fake">

                            <div class="grid gap-4 md:grid-cols-2">
                                <div>
                                    <label class="field-label" for="shipping_recipient_name">اسم المستلم</label>
                                    <input id="shipping_recipient_name" name="shipping[recipient_name]" class="form-input" value="{{ old('shipping.recipient_name', $bookShipping['recipient_name'] ?? $student->name) }}">
                                    @error('shipping.recipient_name') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="field-label" for="shipping_phone">رقم الهاتف</label>
                                    <input id="shipping_phone" name="shipping[phone]" class="form-input" value="{{ old('shipping.phone', $bookShipping['phone'] ?? $student->phone) }}">
                                    @error('shipping.phone') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="field-label" for="shipping_alt_phone">هاتف بديل</label>
                                    <input id="shipping_alt_phone" name="shipping[alternate_phone]" class="form-input" value="{{ old('shipping.alternate_phone', $bookShipping['alternate_phone'] ?? $student->parent_phone) }}">
                                </div>
                                <div>
                                    <label class="field-label" for="shipping_governorate">المحافظة</label>
                                    <input id="shipping_governorate" name="shipping[governorate]" class="form-input" value="{{ old('shipping.governorate', $bookShipping['governorate'] ?? $student->governorate) }}">
                                    @error('shipping.governorate') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="field-label" for="shipping_city">المدينة / المنطقة</label>
                                    <input id="shipping_city" name="shipping[city]" class="form-input" value="{{ old('shipping.city', $bookShipping['city'] ?? '') }}">
                                    @error('shipping.city') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="field-label" for="shipping_address_line1">العنوان الأساسي</label>
                                    <input id="shipping_address_line1" name="shipping[address_line1]" class="form-input" value="{{ old('shipping.address_line1', $bookShipping['address_line1'] ?? '') }}">
                                    @error('shipping.address_line1') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            <div class="grid gap-4 md:grid-cols-2">
                                <div>
                                    <label class="field-label" for="shipping_address_line2">عنوان إضافي</label>
                                    <input id="shipping_address_line2" name="shipping[address_line2]" class="form-input" value="{{ old('shipping.address_line2', $bookShipping['address_line2'] ?? '') }}">
                                </div>
                                <div>
                                    <label class="field-label" for="shipping_landmark">علامة مميزة</label>
                                    <input id="shipping_landmark" name="shipping[landmark]" class="form-input" value="{{ old('shipping.landmark', $bookShipping['landmark'] ?? '') }}">
                                </div>
                            </div>

                            @if ($shipping['warning'])
                                <x-student.notice :title="$shipping['can_deliver'] ? 'مراجعة الشحن' : 'تنبيه على الشحن'" :description="$shipping['warning']" :tone="$shipping['can_deliver'] ? 'warning' : 'danger'" />
                            @endif

                            <button class="btn-primary w-full justify-center">
                                {{ $bookPayment ? 'استكمل الدفع التجريبي لطلب الكتب' : 'ابدأ الدفع التجريبي لطلب الكتب' }}
                            </button>
                        </form>
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
                                <span class="font-semibold">الإجمالي المتوقع</span>
                                <strong class="text-xl text-[var(--color-brand-700)]">{{ number_format($finalTotal) }} {{ $cart->currency }}</strong>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="panel-tight">
                    <p class="text-sm font-semibold text-[var(--color-brand-700)]">بيانات الاستلام الافتراضية</p>
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
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-ink-500)]">ملاحظات</p>
                            <p class="mt-2 leading-8 text-[var(--color-ink-700)]">
                                يمكن تعديل عنوان الشحن عند بدء دفع طلب الكتب، وسيُحفظ snapshot مستقل على الطلب الحالي دون تغيير ملفك الشخصي.
                            </p>
                        </div>
                    </div>

                    <a href="{{ route('student.cart.index') }}" class="btn-secondary mt-5 w-full">العودة إلى السلة</a>
                </section>
            </aside>
        </section>
    </section>
</x-layouts.student>
