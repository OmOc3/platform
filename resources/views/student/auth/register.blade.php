<x-layouts.guest title="إنشاء حساب طالب">
    <div class="grid w-full max-w-7xl gap-8 lg:grid-cols-[0.95fr_1.05fr]">
        <section class="panel p-6 lg:p-8">
            <div class="mb-8">
                <p class="text-sm font-semibold text-[var(--color-brand-700)]">إنشاء حساب جديد</p>
                <h1 class="mt-3 text-3xl font-bold">ابدأ رحلتك داخل البوابة</h1>
                <p class="mt-3 text-sm leading-8 text-[var(--color-ink-700)]">التسجيل الذاتي ينشئ حسابك بحالة pending، ويمكنك بعدها دخول البوابة ومتابعة حالة اعتمادك.</p>
            </div>

            <form method="POST" action="{{ route('student.register.store') }}" class="grid gap-5 md:grid-cols-2">
                @csrf
                <div class="md:col-span-2">
                    <label class="field-label" for="name">الاسم الكامل</label>
                    <input id="name" name="name" value="{{ old('name') }}" class="form-input" required>
                    @error('name') <p class="field-help text-[var(--color-danger)]">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="field-label" for="email">البريد الإلكتروني</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" class="form-input" required>
                    @error('email') <p class="field-help text-[var(--color-danger)]">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="field-label" for="phone">رقم الهاتف</label>
                    <input id="phone" name="phone" value="{{ old('phone') }}" class="form-input" required>
                    @error('phone') <p class="field-help text-[var(--color-danger)]">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="field-label" for="parent_phone">هاتف ولي الأمر</label>
                    <input id="parent_phone" name="parent_phone" value="{{ old('parent_phone') }}" class="form-input">
                    @error('parent_phone') <p class="field-help text-[var(--color-danger)]">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="field-label" for="governorate">المحافظة</label>
                    <input id="governorate" name="governorate" value="{{ old('governorate') }}" class="form-input">
                    @error('governorate') <p class="field-help text-[var(--color-danger)]">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="field-label" for="grade_id">الصف</label>
                    <select id="grade_id" name="grade_id" class="form-select" required>
                        <option value="">اختر الصف</option>
                        @foreach ($grades as $grade)
                            <option value="{{ $grade->id }}" @selected((int) old('grade_id') === $grade->id)>{{ $grade->name_ar }}</option>
                        @endforeach
                    </select>
                    @error('grade_id') <p class="field-help text-[var(--color-danger)]">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="field-label" for="track_id">المسار</label>
                    <select id="track_id" name="track_id" class="form-select" required>
                        <option value="">اختر المسار</option>
                        @foreach ($tracks as $track)
                            <option value="{{ $track->id }}" @selected((int) old('track_id') === $track->id)>{{ $track->name_ar }}</option>
                        @endforeach
                    </select>
                    @error('track_id') <p class="field-help text-[var(--color-danger)]">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="field-label" for="password">كلمة المرور</label>
                    <input id="password" type="password" name="password" class="form-input" required>
                    @error('password') <p class="field-help text-[var(--color-danger)]">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="field-label" for="password_confirmation">تأكيد كلمة المرور</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" class="form-input" required>
                </div>
                <div class="md:col-span-2 flex items-center gap-3">
                    <input id="is_azhar" type="checkbox" name="is_azhar" value="1" class="h-4 w-4 rounded border-[var(--color-brand-200)] text-[var(--color-brand-700)]" @checked(old('is_azhar'))>
                    <label for="is_azhar" class="text-sm text-[var(--color-ink-700)]">طالب أزهري</label>
                </div>
                <div class="md:col-span-2 flex flex-wrap gap-3 pt-2">
                    <button type="submit" class="btn-primary">إنشاء الحساب</button>
                    <a href="{{ route('student.login') }}" class="btn-secondary">لديك حساب بالفعل؟</a>
                </div>
            </form>
        </section>

        <section class="hidden surface-card-soft rounded-[2rem] p-8 lg:block">
            <p class="font-display text-4xl text-[var(--color-brand-700)]">قبل أن تبدأ</p>
            <div class="mt-8 space-y-4">
                <article class="surface-card rounded-[1.8rem] p-5">
                    <p class="text-sm font-semibold text-[var(--color-brand-700)]">١. أنشئ الحساب</p>
                    <p class="mt-3 text-sm leading-8 text-[var(--color-ink-700)]">أدخل بياناتك الأساسية وحدد الصف والمسار المناسبين.</p>
                </article>
                <article class="surface-card rounded-[1.8rem] p-5">
                    <p class="text-sm font-semibold text-[var(--color-brand-700)]">٢. تابع حالة الطلب</p>
                    <p class="mt-3 text-sm leading-8 text-[var(--color-ink-700)]">بعد التسجيل، تستطيع دخول البوابة ومتابعة حالة المراجعة بدون انتظار الدعم يدويًا.</p>
                </article>
                <article class="surface-card rounded-[1.8rem] p-5">
                    <p class="text-sm font-semibold text-[var(--color-brand-700)]">٣. استخدم البوابة</p>
                    <p class="mt-3 text-sm leading-8 text-[var(--color-ink-700)]">ستظهر لك بيانات الحساب، المدفوعات، الحضور، وسجل الشكاوى في مكان واحد.</p>
                </article>
            </div>
        </section>
    </div>
</x-layouts.guest>
