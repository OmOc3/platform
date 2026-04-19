<x-layouts.guest title="إعادة تعيين كلمة المرور">
    <section class="panel w-full max-w-xl p-6 lg:p-8">
        <p class="text-sm font-semibold text-[var(--color-brand-700)]">إعادة تعيين كلمة المرور</p>
        <h1 class="mt-3 text-3xl font-bold">اختر كلمة مرور جديدة</h1>

        <form method="POST" action="{{ route('student.password.store') }}" class="mt-8 space-y-5">
            @csrf
            <input type="hidden" name="token" value="{{ $request->route('token') }}">
            <div>
                <label class="field-label" for="email">البريد الإلكتروني</label>
                <input id="email" type="email" name="email" value="{{ old('email', $request->email) }}" class="form-input" required autofocus>
                @error('email') <p class="field-help text-[var(--color-danger)]">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="field-label" for="password">كلمة المرور الجديدة</label>
                <input id="password" type="password" name="password" class="form-input" required>
                @error('password') <p class="field-help text-[var(--color-danger)]">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="field-label" for="password_confirmation">تأكيد كلمة المرور</label>
                <input id="password_confirmation" type="password" name="password_confirmation" class="form-input" required>
            </div>
            <button type="submit" class="btn-primary w-full">تحديث كلمة المرور</button>
        </form>
    </section>
</x-layouts.guest>
