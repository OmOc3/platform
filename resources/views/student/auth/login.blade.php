<x-layouts.guest title="دخول الطالب">
    <div class="grid w-full max-w-6xl gap-8 lg:grid-cols-[1fr_460px]">
        <section class="hidden rounded-[2rem] bg-[var(--color-brand-700)] p-10 text-white lg:block">
            <p class="font-display text-4xl">بوابة الطالب</p>
            <h1 class="mt-6 text-4xl font-bold leading-tight">سجّل دخولك للوصول إلى حالة حسابك وسجل الطلبات والحضور والدعم.</h1>
            <p class="mt-6 max-w-md text-base leading-8 text-white/80">حتى في حالة المراجعة، تظل البوابة متاحة لمتابعة الطلب والحساب والبيانات الأساسية إلى حين اعتماد الاشتراك.</p>
        </section>

        <section class="panel p-6 lg:p-8">
            <div class="mb-8">
                <p class="text-sm font-semibold text-[var(--color-brand-700)]">تسجيل دخول الطالب</p>
                <h2 class="mt-3 text-3xl font-bold">مرحبًا بعودتك</h2>
                <p class="mt-3 text-sm leading-8 text-[var(--color-ink-700)]">يمكنك أيضًا <a href="{{ route('student.register') }}" class="font-semibold text-[var(--color-brand-700)]">إنشاء حساب جديد</a>.</p>
            </div>

            <form method="POST" action="{{ route('student.login.store') }}" class="space-y-5">
                @csrf
                <div>
                    <label for="email" class="field-label">البريد الإلكتروني</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" class="form-input" required autofocus>
                    @error('email') <p class="field-help text-[var(--color-danger)]">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="password" class="field-label">كلمة المرور</label>
                    <input id="password" type="password" name="password" class="form-input" required>
                    @error('password') <p class="field-help text-[var(--color-danger)]">{{ $message }}</p> @enderror
                </div>
                <label class="flex items-center gap-3 text-sm text-[var(--color-ink-700)]">
                    <input type="checkbox" name="remember" value="1" class="h-4 w-4 rounded border-[var(--color-brand-200)] text-[var(--color-brand-700)]">
                    تذكرني
                </label>
                <button type="submit" class="btn-primary w-full">دخول</button>
            </form>

            <div class="mt-6 text-sm">
                <a href="{{ route('student.password.request') }}" class="font-semibold text-[var(--color-brand-700)]">نسيت كلمة المرور؟</a>
            </div>
        </section>
    </div>
</x-layouts.guest>
