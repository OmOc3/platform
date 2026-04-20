<x-layouts.guest title="دخول الإدارة">
    <div class="grid w-full max-w-6xl gap-8 lg:grid-cols-[1fr_460px]">
        <section class="hidden ink-panel lg:block">
            <p class="font-display text-4xl">بوابة الإدارة</p>
            <h1 class="mt-6 text-4xl font-bold leading-tight">إدارة الأكاديمية من لوحة واحدة تجمع التشغيل والأكاديمي والحوكمة.</h1>
            <p class="mt-6 max-w-md text-base leading-8 text-white/80">
                تسجيل الدخول هنا مخصص لفريق الإدارة والتشغيل فقط. واجهة الطالب ومسارات الشراء والمحتوى تأتي في المراحل التالية من البناء.
            </p>
        </section>

        <section class="panel p-6 lg:p-8">
            <div class="mb-8">
                <p class="section-kicker">تسجيل دخول الإدارة</p>
                <h2 class="mt-3 text-3xl font-bold">مرحبًا بعودتك</h2>
            </div>

            <form method="POST" action="{{ route('admin.login.store') }}" class="space-y-5">
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
        </section>
    </div>
</x-layouts.guest>
