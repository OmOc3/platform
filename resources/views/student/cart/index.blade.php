<x-layouts.student title="السلة" heading="سلة المشتريات" subheading="إدارة عناصر الشراء قبل تجهيز مسودات الطلبات الرقمية وطلبات الكتب بشكل منفصل.">
    <section class="space-y-6">
        @if ($cart->items->isEmpty())
            <x-student.empty-state title="السلة فارغة" description="أضف محاضرة أو باقة أو كتابًا من صفحات الكتالوج لتظهر هنا." />
        @else
            <section class="panel-tight">
                <div class="flex items-end justify-between gap-4">
                    <div>
                        <p class="text-sm font-semibold text-[var(--color-brand-700)]">العناصر الرقمية</p>
                        <p class="mt-2 text-sm text-[var(--color-ink-700)]">تشمل المحاضرات والباقات الرقمية.</p>
                    </div>
                    <span class="text-sm font-semibold">{{ number_format($digitalTotal) }} {{ $cart->currency }}</span>
                </div>

                @if ($digitalItems->isEmpty())
                    <div class="mt-4">
                        <x-student.empty-state title="لا توجد عناصر رقمية" description="أضف محتوى رقميًا من الكتالوج ليظهر هنا." />
                    </div>
                @else
                    <div class="mt-4 overflow-x-auto">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>العنصر</th>
                                    <th>النوع</th>
                                    <th>السعر</th>
                                    <th>الإجراء</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($digitalItems as $item)
                                    <tr>
                                        <td class="font-semibold">{{ $item->product?->name_ar }}</td>
                                        <td>{{ $item->product?->kind->value }}</td>
                                        <td>{{ number_format($item->total_price_amount) }} {{ $cart->currency }}</td>
                                        <td>
                                            <form method="POST" action="{{ route('student.cart.destroy', $item) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn-danger">حذف</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>

            <section class="panel-tight">
                <div class="flex items-end justify-between gap-4">
                    <div>
                        <p class="text-sm font-semibold text-[var(--color-brand-700)]">الكتب</p>
                        <p class="mt-2 text-sm text-[var(--color-ink-700)]">يمكن تعديل الكمية هنا قبل تجهيز الطلب.</p>
                    </div>
                    <span class="text-sm font-semibold">{{ number_format($bookTotal) }} {{ $cart->currency }}</span>
                </div>

                @if ($bookItems->isEmpty())
                    <div class="mt-4">
                        <x-student.empty-state title="لا توجد كتب في السلة" description="أضف كتابًا من قسم الكتب لتجهيزه ضمن طلب منفصل." />
                    </div>
                @else
                    <div class="mt-4 overflow-x-auto">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>الكتاب</th>
                                    <th>الكمية</th>
                                    <th>الإجمالي</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($bookItems as $item)
                                    <tr>
                                        <td class="font-semibold">{{ $item->product?->name_ar }}</td>
                                        <td>
                                            <form method="POST" action="{{ route('student.cart.update', $item) }}" class="flex items-center gap-2">
                                                @csrf
                                                @method('PUT')
                                                <input type="number" min="1" max="10" name="quantity" value="{{ $item->quantity }}" class="form-input max-w-24">
                                                <button class="btn-secondary !px-4 !py-2">تحديث</button>
                                            </form>
                                        </td>
                                        <td>{{ number_format($item->total_price_amount) }} {{ $cart->currency }}</td>
                                        <td>
                                            <form method="POST" action="{{ route('student.cart.destroy', $item) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn-danger">حذف</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>

            <section class="panel-tight flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-sm font-semibold text-[var(--color-brand-700)]">الإجمالي الحالي</p>
                    <p class="mt-2 text-2xl font-bold">{{ number_format($grandTotal) }} {{ $cart->currency }}</p>
                </div>
                <a href="{{ route('student.checkout.show') }}" class="btn-primary">تجهيز الطلبات</a>
            </section>
        @endif
    </section>
</x-layouts.student>
