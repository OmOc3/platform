<x-layouts.guest title="استعادة كلمة المرور">
    <section class="panel w-full max-w-xl p-6 lg:p-8">
        <p class="text-sm font-semibold text-[var(--color-brand-700)]">استعادة كلمة المرور</p>
        <h1 class="mt-3 text-3xl font-bold">أدخل بريدك الإلكتروني</h1>
        <p class="mt-3 text-sm leading-8 text-[var(--color-ink-700)]">إذا كان الحساب موجودًا، فسيتم إرسال رابط إعادة تعيين كلمة المرور إليه.</p>

        <form method="POST" action="{{ route('student.password.email') }}" class="mt-8 space-y-5">
            @csrf
            <div>
                <label class="field-label" for="email">البريد الإلكتروني</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" class="form-input" required autofocus>
                @error('email') <p class="field-help text-[var(--color-danger)]">{{ $message }}</p> @enderror
            </div>
            <button type="submit" class="btn-primary w-full">إرسال رابط الاستعادة</button>
        </form>
    </section>
</x-layouts.guest>
