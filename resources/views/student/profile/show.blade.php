<x-layouts.student title="حسابي" heading="حسابي" subheading="تحديث بيانات الطالب الأساسية ومراجعة الملف الشخصي.">
    <div class="grid gap-6 lg:grid-cols-[0.9fr_1.1fr]">
        <section class="panel-tight">
            <p class="text-sm font-semibold text-[var(--color-brand-700)]">ملخص الحساب</p>
            <dl class="mt-5 space-y-4 text-sm">
                <div class="flex items-start justify-between gap-4 border-b border-[color-mix(in_oklch,var(--color-brand-100)_82%,white)] pb-4">
                    <dt class="text-[var(--color-ink-500)]">الاسم</dt>
                    <dd class="font-semibold">{{ $student->name }}</dd>
                </div>
                <div class="flex items-start justify-between gap-4 border-b border-[color-mix(in_oklch,var(--color-brand-100)_82%,white)] pb-4">
                    <dt class="text-[var(--color-ink-500)]">رقم الطالب</dt>
                    <dd class="font-semibold">{{ $student->student_number }}</dd>
                </div>
                <div class="flex items-start justify-between gap-4 border-b border-[color-mix(in_oklch,var(--color-brand-100)_82%,white)] pb-4">
                    <dt class="text-[var(--color-ink-500)]">البريد الإلكتروني</dt>
                    <dd class="font-semibold">{{ $student->email }}</dd>
                </div>
                <div class="flex items-start justify-between gap-4 border-b border-[color-mix(in_oklch,var(--color-brand-100)_82%,white)] pb-4">
                    <dt class="text-[var(--color-ink-500)]">المحافظة</dt>
                    <dd class="font-semibold">{{ $student->governorate ?: '—' }}</dd>
                </div>
                <div class="flex items-start justify-between gap-4 border-b border-[color-mix(in_oklch,var(--color-brand-100)_82%,white)] pb-4">
                    <dt class="text-[var(--color-ink-500)]">المجموعة</dt>
                    <dd class="font-semibold">{{ $student->group?->name_ar ?: '—' }}</dd>
                </div>
                <div class="flex items-start justify-between gap-4 border-b border-[color-mix(in_oklch,var(--color-brand-100)_82%,white)] pb-4">
                    <dt class="text-[var(--color-ink-500)]">الهاتف</dt>
                    <dd class="font-semibold">{{ $student->phone ?: '—' }}</dd>
                </div>
                <div class="flex items-start justify-between gap-4">
                    <dt class="text-[var(--color-ink-500)]">هاتف ولي الأمر</dt>
                    <dd class="font-semibold">{{ $student->parent_phone ?: '—' }}</dd>
                </div>
            </dl>
        </section>

        <section class="panel-tight">
            <p class="text-sm font-semibold text-[var(--color-brand-700)]">تعديل البيانات</p>
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
                    <label class="field-label" for="phone">رقم الهاتف</label>
                    <input id="phone" name="phone" value="{{ old('phone', $student->phone) }}" class="form-input" required>
                    @error('phone') <p class="field-help text-[var(--color-danger)]">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="field-label" for="parent_phone">هاتف ولي الأمر</label>
                    <input id="parent_phone" name="parent_phone" value="{{ old('parent_phone', $student->parent_phone) }}" class="form-input">
                    @error('parent_phone') <p class="field-help text-[var(--color-danger)]">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="field-label" for="governorate">المحافظة</label>
                    <input id="governorate" name="governorate" value="{{ old('governorate', $student->governorate) }}" class="form-input">
                    @error('governorate') <p class="field-help text-[var(--color-danger)]">{{ $message }}</p> @enderror
                </div>
                <div class="md:col-span-2">
                    <button type="submit" class="btn-primary">حفظ التعديلات</button>
                </div>
            </form>
        </section>
    </div>
</x-layouts.student>
