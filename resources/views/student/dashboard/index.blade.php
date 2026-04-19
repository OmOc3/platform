<x-layouts.student title="الرئيسية" heading="الرئيسية" subheading="متابعة سريعة لحالة الحساب والاشتراكات والأنشطة الأخيرة.">
    <section class="space-y-6">
        @if ($notices !== [])
            <div class="grid gap-4">
                @foreach ($notices as $notice)
                    <x-student.notice :title="$notice['title']" :body="$notice['body']" :tone="$notice['tone']" />
                @endforeach
            </div>
        @endif

        <div class="grid gap-4 md:grid-cols-3">
            @foreach ($stats as $stat)
                <x-student.summary-card :label="$stat['label']" :value="$stat['value']" />
            @endforeach
        </div>

        <div class="grid gap-4 lg:grid-cols-4">
            <a href="{{ route('student.profile.show') }}" class="panel-tight">
                <p class="text-sm font-semibold text-[var(--color-brand-700)]">حسابي</p>
                <p class="mt-3 text-sm leading-8 text-[var(--color-ink-700)]">عرض وتحديث البيانات الأساسية.</p>
            </a>
            <a href="{{ route('student.payments.index') }}" class="panel-tight">
                <p class="text-sm font-semibold text-[var(--color-brand-700)]">المدفوعات الرقمية</p>
                <p class="mt-3 text-sm leading-8 text-[var(--color-ink-700)]">سجل الاستحقاقات والمدفوعات والمنح.</p>
            </a>
            <a href="{{ route('student.book-orders.index') }}" class="panel-tight">
                <p class="text-sm font-semibold text-[var(--color-brand-700)]">طلبات الكتب</p>
                <p class="mt-3 text-sm leading-8 text-[var(--color-ink-700)]">عرض الطلبات الحالية والسابقة.</p>
            </a>
            <a href="{{ route('student.complaints.index') }}" class="panel-tight">
                <p class="text-sm font-semibold text-[var(--color-brand-700)]">الشكاوى والاقتراحات</p>
                <p class="mt-3 text-sm leading-8 text-[var(--color-ink-700)]">أرسل ملاحظتك وتابع السجل.</p>
            </a>
        </div>

        <section class="panel-tight">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="text-sm font-semibold text-[var(--color-brand-700)]">أحدث الباقات</p>
                    <p class="mt-2 text-sm leading-8 text-[var(--color-ink-700)]">عرض سريع للباقات المميزة المتاحة حاليًا.</p>
                </div>
                <a href="{{ route('student.packages.index') }}" class="btn-secondary">عرض القسم</a>
            </div>

            <div class="mt-6 grid gap-4 lg:grid-cols-3">
                @foreach ($latestPackages as $product)
                    <article class="rounded-[1.8rem] bg-white p-5 ring-1 ring-[color-mix(in_oklch,var(--color-brand-100)_82%,white)]">
                        <p class="text-sm font-semibold text-[var(--color-brand-700)]">{{ $product->name_ar }}</p>
                        <p class="mt-3 text-sm leading-8 text-[var(--color-ink-700)]">{{ $product->teaser }}</p>
                        <div class="mt-4 flex items-center justify-between">
                            <span class="text-xs text-[var(--color-ink-500)]">{{ $product->package?->billing_cycle_label }}</span>
                            <span class="font-semibold">{{ number_format($product->price_amount) }} ج</span>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>

        <section class="panel-tight">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="text-sm font-semibold text-[var(--color-brand-700)]">أحدث محتوى متاح لك</p>
                    <p class="mt-2 text-sm leading-8 text-[var(--color-ink-700)]">يظهر هنا ما تم تفعيله لك عبر شراء مباشر أو منحة أو باقة.</p>
                </div>
                <a href="{{ route('student.payments.index') }}" class="btn-secondary">عرض السجل</a>
            </div>

            @if ($latestAccessibleContent->isEmpty())
                <div class="mt-6">
                    <x-student.empty-state title="لا يوجد محتوى مفعّل بعد" description="عند تفعيل أي باقة أو وصول رقمي سيظهر هنا تلقائيًا." />
                </div>
            @else
                <div class="mt-6 grid gap-4 lg:grid-cols-2">
                    @foreach ($latestAccessibleContent as $entitlement)
                        <article class="rounded-[1.8rem] bg-white p-5 ring-1 ring-[color-mix(in_oklch,var(--color-brand-100)_82%,white)]">
                            <p class="text-sm font-semibold text-[var(--color-brand-700)]">{{ $entitlement->item_name_snapshot }}</p>
                            <p class="mt-2 text-xs text-[var(--color-ink-500)]">{{ $entitlement->source->value }}</p>
                            <div class="mt-4 flex items-center justify-between">
                                <span class="text-sm text-[var(--color-ink-700)]">{{ optional($entitlement->granted_at)->format('Y-m-d') }}</span>
                                <span class="rounded-full bg-[var(--color-brand-50)] px-3 py-1 text-xs font-semibold text-[var(--color-brand-700)]">{{ number_format($entitlement->price_amount) }} ج</span>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>
    </section>
</x-layouts.student>
