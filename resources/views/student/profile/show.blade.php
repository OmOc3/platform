<x-layouts.student title="حسابي" heading="حسابي" subheading="إدارة بيانات الطالب الأساسية ومراجعة الملف الشخصي وسجل الاستخدام من مكان واحد.">
    @php($initials = \Illuminate\Support\Str::of($student->name)->trim()->explode(' ')->take(2)->map(fn ($part) => \Illuminate\Support\Str::substr($part, 0, 1))->implode(''))

    <section class="space-y-6">
        <x-student.account-nav current="profile" />

        <section class="grid gap-6 xl:grid-cols-[1.15fr_0.85fr]">
            <article class="panel-tight">
                <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                    <div class="flex items-center gap-4">
                        <div class="portal-avatar">{{ $initials }}</div>
                        <div>
                            <p class="section-kicker">الملف الشخصي</p>
                            <h2 class="mt-2 text-2xl font-bold lg:text-3xl">{{ $student->name }}</h2>
                            <p class="mt-2 text-sm text-[var(--color-ink-500)]">رقم الطالب {{ $student->student_number }}</p>
                        </div>
                    </div>

                    <a href="#profile-edit" class="btn-primary">تعديل البيانات</a>
                </div>

                <div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                    <div class="portal-summary-card">
                        <span class="portal-summary-card__label">الصف</span>
                        <strong class="portal-summary-card__value">{{ $student->grade?->name_ar ?? '—' }}</strong>
                    </div>
                    <div class="portal-summary-card">
                        <span class="portal-summary-card__label">المسار</span>
                        <strong class="portal-summary-card__value">{{ $student->track?->name_ar ?? '—' }}</strong>
                    </div>
                    <div class="portal-summary-card">
                        <span class="portal-summary-card__label">السنتر</span>
                        <strong class="portal-summary-card__value">{{ $student->center?->name_ar ?? $student->group?->center?->name_ar ?? '—' }}</strong>
                    </div>
                    <div class="portal-summary-card">
                        <span class="portal-summary-card__label">المجموعة</span>
                        <strong class="portal-summary-card__value">{{ $student->group?->name_ar ?? '—' }}</strong>
                    </div>
                </div>
            </article>

            <aside class="panel-tight">
                <p class="section-kicker">روابط الحساب</p>
                <div class="mt-5 grid gap-3">
                    <a href="{{ route('student.payments.index') }}" class="portal-menu-link">مدفوعات المحاضرات</a>
                    <a href="{{ route('student.book-orders.index') }}" class="portal-menu-link">مدفوعات الكتب</a>
                    <a href="{{ route('student.attendance.index') }}" class="portal-menu-link">حضور السنتر</a>
                    <a href="{{ route('student.cart.index') }}" class="portal-menu-link">بيانات الاستلام والسلة</a>
                </div>

                <div class="surface-inset mt-6 rounded-[1.3rem] p-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-ink-500)]">ملحوظة</p>
                    <p class="mt-3 text-sm leading-8 text-[var(--color-ink-700)]">
                        استخدم هذه الصفحة لتحديث الهاتف والمحافظة وملاحظات الاستلام حتى تنعكس مباشرةً على صفحات السلة والطلبات.
                    </p>
                </div>
            </aside>
        </section>

        <section class="grid gap-6 xl:grid-cols-[0.9fr_1.1fr]">
            <article class="panel-tight">
                <p class="section-kicker">بيانات الحساب الحالية</p>

                <dl class="mt-5 space-y-4 text-sm">
                    <div class="flex items-start justify-between gap-4 border-b border-[var(--color-border-soft)] pb-4">
                        <dt class="text-[var(--color-ink-500)]">الاسم</dt>
                        <dd class="font-semibold">{{ $student->name }}</dd>
                    </div>
                    <div class="flex items-start justify-between gap-4 border-b border-[var(--color-border-soft)] pb-4">
                        <dt class="text-[var(--color-ink-500)]">رقم الطالب</dt>
                        <dd class="font-semibold">{{ $student->student_number }}</dd>
                    </div>
                    <div class="flex items-start justify-between gap-4 border-b border-[var(--color-border-soft)] pb-4">
                        <dt class="text-[var(--color-ink-500)]">البريد الإلكتروني</dt>
                        <dd class="font-semibold">{{ $student->email }}</dd>
                    </div>
                    <div class="flex items-start justify-between gap-4 border-b border-[var(--color-border-soft)] pb-4">
                        <dt class="text-[var(--color-ink-500)]">المحافظة</dt>
                        <dd class="font-semibold">{{ $student->governorate ?: '—' }}</dd>
                    </div>
                    <div class="flex items-start justify-between gap-4 border-b border-[var(--color-border-soft)] pb-4">
                        <dt class="text-[var(--color-ink-500)]">المجموعة</dt>
                        <dd class="font-semibold">{{ $student->group?->name_ar ?: '—' }}</dd>
                    </div>
                    <div class="flex items-start justify-between gap-4 border-b border-[var(--color-border-soft)] pb-4">
                        <dt class="text-[var(--color-ink-500)]">الهاتف</dt>
                        <dd class="font-semibold">{{ $student->phone ?: '—' }}</dd>
                    </div>
                    <div class="flex items-start justify-between gap-4 border-b border-[var(--color-border-soft)] pb-4">
                        <dt class="text-[var(--color-ink-500)]">هاتف ولي الأمر</dt>
                        <dd class="font-semibold">{{ $student->parent_phone ?: '—' }}</dd>
                    </div>
                    <div class="flex items-start justify-between gap-4">
                        <dt class="text-[var(--color-ink-500)]">ملاحظات</dt>
                        <dd class="max-w-[24rem] text-left leading-8 text-[var(--color-ink-700)]">{{ $student->notes ?: 'لا توجد ملاحظات مسجلة بعد.' }}</dd>
                    </div>
                </dl>
            </article>

            <section id="profile-edit" class="panel-tight">
                <p class="section-kicker">تعديل البيانات</p>
                <form method="POST" action="{{ route('student.profile.update') }}" class="mt-5 grid gap-5 md:grid-cols-2">
                    @csrf
                    @method('PUT')

                    <div class="md:col-span-2">
                        <label class="field-label" for="name">الاسم الكامل</label>
                        <input id="name" name="name" value="{{ old('name', $student->name) }}" class="form-input" required>
                        @error('name') <p class="field-help text-[var(--color-danger)]">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="field-label" for="email">البريد الإلكتروني</label>
                        <input id="email" type="email" name="email" value="{{ old('email', $student->email) }}" class="form-input" required>
                        @error('email') <p class="field-help text-[var(--color-danger)]">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="field-label" for="governorate">المحافظة</label>
                        <input id="governorate" name="governorate" value="{{ old('governorate', $student->governorate) }}" class="form-input">
                        @error('governorate') <p class="field-help text-[var(--color-danger)]">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="field-label" for="phone">رقم الهاتف</label>
                        <input id="phone" name="phone" value="{{ old('phone', $student->phone) }}" class="form-input" required>
                        @error('phone') <p class="field-help text-[var(--color-danger)]">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="field-label" for="parent_phone">هاتف ولي الأمر</label>
                        <input id="parent_phone" name="parent_phone" value="{{ old('parent_phone', $student->parent_phone) }}" class="form-input">
                        @error('parent_phone') <p class="field-help text-[var(--color-danger)]">{{ $message }}</p> @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="field-label" for="notes">ملاحظات إضافية</label>
                        <textarea id="notes" name="notes" class="form-textarea" placeholder="مثل: عنوان قريب للاستلام، ملاحظة عن أوقات التواصل، أو أي تفاصيل مهمة للطلب.">{{ old('notes', $student->notes) }}</textarea>
                        @error('notes') <p class="field-help text-[var(--color-danger)]">{{ $message }}</p> @enderror
                    </div>

                    <div class="md:col-span-2 flex flex-wrap gap-3">
                        <button type="submit" class="btn-primary">حفظ التعديلات</button>
                        <a href="{{ route('student.cart.index') }}" class="btn-secondary">العودة إلى السلة</a>
                    </div>
                </form>
            </section>
        </section>
    </section>
</x-layouts.student>
