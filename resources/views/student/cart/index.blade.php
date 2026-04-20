<x-layouts.student title="السلة" heading="السلة" subheading="إدارة العناصر الرقمية وطلبات الكتب مع مراجعة بيانات الاستلام قبل تجهيز الطلبات النهائية.">
    <section class="space-y-6">
        @if ($cart->items->isEmpty())
            <section class="panel-tight">
                <x-student.empty-state title="السلة فارغة" description="أضف محاضرة أو باقة أو كتابًا من صفحات الكتالوج لتظهر هنا." />
                <div class="mt-5 flex flex-wrap gap-3">
                    <a href="{{ route('student.lectures.index') }}" class="btn-primary">استعرض المحاضرات</a>
                    <a href="{{ route('student.books.index') }}" class="btn-secondary">استعرض الكتب</a>
                </div>
            </section>
        @else
            @if ($shipping['warning'])
                <section class="rounded-[2rem] bg-[color-mix(in_oklch,var(--color-violet-100)_60%,white)] px-5 py-4 text-[var(--color-violet-700)] shadow-[0_18px_40px_rgba(71,58,29,0.06)]">
                    <p class="text-sm font-bold">تنبيه الشحن</p>
                    <p class="mt-2 text-sm leading-8">{{ $shipping['warning'] }}</p>
                </section>
            @endif

            <section class="grid gap-6 xl:grid-cols-[1.22fr_0.78fr]">
                <div class="space-y-6">
                    <section class="panel-tight">
                        <div class="flex items-end justify-between gap-4">
                            <div>
                                <p class="text-sm font-semibold text-[var(--color-brand-700)]">العناصر الرقمية</p>
                                <p class="mt-2 text-sm text-[var(--color-ink-700)]">تشمل المحاضرات والباقات الرقمية وتُجهز كطلب منفصل.</p>
                            </div>
                            <span class="text-sm font-semibold">{{ number_format($digitalTotal) }} {{ $cart->currency }}</span>
                        </div>

                        @if ($digitalItems->isEmpty())
                            <div class="mt-4">
                                <x-student.empty-state title="لا توجد عناصر رقمية" description="أضف محتوى رقميًا من الكتالوج ليظهر هنا." />
                            </div>
                        @else
                            <div class="mt-5 space-y-3">
                                @foreach ($digitalItems as $item)
                                    <article class="surface-card rounded-[1.8rem] p-4">
                                        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                                            <div>
                                                <div class="flex flex-wrap items-center gap-2">
                                                    <x-admin.status-badge :label="$item->product?->kind?->label() ?? 'عنصر رقمي'" />
                                                </div>
                                                <h3 class="mt-3 font-semibold">{{ $item->product?->name_ar }}</h3>
                                                <p class="mt-2 text-sm text-[var(--color-ink-500)]">{{ $item->product?->teaser }}</p>
                                            </div>

                                            <div class="flex flex-wrap items-center gap-3">
                                                <span class="text-lg font-bold text-[var(--color-brand-700)]">{{ number_format($item->total_price_amount) }} {{ $cart->currency }}</span>
                                                <form method="POST" action="{{ route('student.cart.destroy', $item) }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="btn-danger">حذف</button>
                                                </form>
                                            </div>
                                        </div>
                                    </article>
                                @endforeach
                            </div>
                        @endif
                    </section>

                    <section class="panel-tight">
                        <div class="flex items-end justify-between gap-4">
                            <div>
                                <p class="text-sm font-semibold text-[var(--color-brand-700)]">الكتب</p>
                                <p class="mt-2 text-sm text-[var(--color-ink-700)]">يمكن تعديل الكمية هنا قبل تجهيز طلب الكتب والشحن.</p>
                            </div>
                            <span class="text-sm font-semibold">{{ number_format($bookTotal) }} {{ $cart->currency }}</span>
                        </div>

                        @if ($bookItems->isEmpty())
                            <div class="mt-4">
                                <x-student.empty-state title="لا توجد كتب في السلة" description="أضف كتابًا من قسم الكتب لتجهيزه ضمن طلب منفصل." />
                            </div>
                        @else
                            <div class="mt-5 space-y-3">
                                @foreach ($bookItems as $item)
                                    <article class="surface-card rounded-[1.8rem] p-4">
                                        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                                            <div>
                                                <h3 class="font-semibold">{{ $item->product?->name_ar }}</h3>
                                                <p class="mt-2 text-sm text-[var(--color-ink-500)]">{{ $item->product?->teaser }}</p>
                                            </div>

                                            <div class="flex flex-col gap-3 lg:items-end">
                                                <form method="POST" action="{{ route('student.cart.update', $item) }}" class="flex flex-wrap items-center gap-2">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="number" min="1" max="10" name="quantity" value="{{ $item->quantity }}" class="form-input max-w-24">
                                                    <button class="btn-secondary !px-4 !py-2">تحديث</button>
                                                </form>

                                                <div class="flex items-center gap-3">
                                                    <span class="text-lg font-bold text-[var(--color-brand-700)]">{{ number_format($item->total_price_amount) }} {{ $cart->currency }}</span>
                                                    <form method="POST" action="{{ route('student.cart.destroy', $item) }}">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button class="btn-danger">حذف</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </article>
                                @endforeach
                            </div>
                        @endif
                    </section>
                </div>

                <aside class="space-y-6">
                    <section class="panel-tight">
                        <p class="text-sm font-semibold text-[var(--color-brand-700)]">ملخص الطلب</p>
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
                                    <span class="font-semibold">الإجمالي النهائي</span>
                                    <strong class="text-xl text-[var(--color-brand-700)]">{{ number_format($finalTotal) }} {{ $cart->currency }}</strong>
                                </div>
                            </div>
                        </div>

                        <a href="{{ route('student.checkout.show') }}" class="btn-primary mt-6 w-full">إتمام الطلب</a>
                    </section>

                    <section class="panel-tight">
                        <p class="text-sm font-semibold text-[var(--color-brand-700)]">بيانات الاستلام المحفوظة</p>
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
                                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-ink-500)]">ملاحظات الاستلام</p>
                                <p class="mt-2 leading-8 text-[var(--color-ink-700)]">{{ $student->notes ?: 'يمكنك إضافة عنوان تفصيلي أو ملاحظة تسليم من النموذج أدناه.' }}</p>
                            </div>
                        </div>

                        <details class="mt-5">
                            <summary class="portal-nav-utility w-full cursor-pointer justify-center">إضافة / تعديل بيانات الاستلام</summary>
                            <form method="POST" action="{{ route('student.profile.update') }}" class="mt-4 space-y-4">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="redirect_to" value="{{ route('student.cart.index') }}">
                                <div>
                                    <label class="field-label" for="cart_name">الاسم</label>
                                    <input id="cart_name" name="name" value="{{ old('name', $student->name) }}" class="form-input" required>
                                </div>
                                <div>
                                    <label class="field-label" for="cart_email">البريد الإلكتروني</label>
                                    <input id="cart_email" type="email" name="email" value="{{ old('email', $student->email) }}" class="form-input" required>
                                </div>
                                <div>
                                    <label class="field-label" for="cart_phone">رقم الهاتف</label>
                                    <input id="cart_phone" name="phone" value="{{ old('phone', $student->phone) }}" class="form-input" required>
                                </div>
                                <div>
                                    <label class="field-label" for="cart_parent_phone">هاتف ولي الأمر</label>
                                    <input id="cart_parent_phone" name="parent_phone" value="{{ old('parent_phone', $student->parent_phone) }}" class="form-input">
                                </div>
                                <div>
                                    <label class="field-label" for="cart_governorate">المحافظة</label>
                                    <input id="cart_governorate" name="governorate" value="{{ old('governorate', $student->governorate) }}" class="form-input">
                                </div>
                                <div>
                                    <label class="field-label" for="cart_notes">عنوان تفصيلي / ملاحظات التسليم</label>
                                    <textarea id="cart_notes" name="notes" class="form-textarea">{{ old('notes', $student->notes) }}</textarea>
                                </div>
                                <button class="btn-primary w-full">حفظ بيانات الاستلام</button>
                            </form>
                        </details>
                    </section>
                </aside>
            </section>
        @endif
    </section>
</x-layouts.student>
