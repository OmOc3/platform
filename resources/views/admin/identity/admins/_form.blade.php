@csrf
@if($adminUser->exists)
    @method('PUT')
@endif

<div class="grid gap-5 md:grid-cols-2">
    <div>
        <label class="field-label" for="name">الاسم</label>
        <input id="name" name="name" value="{{ old('name', $adminUser->name) }}" class="form-input" required>
        @error('name') <p class="field-help text-[var(--color-danger)]">{{ $message }}</p> @enderror
    </div>
    <div>
        <label class="field-label" for="email">البريد الإلكتروني</label>
        <input id="email" type="email" name="email" value="{{ old('email', $adminUser->email) }}" class="form-input" required>
        @error('email') <p class="field-help text-[var(--color-danger)]">{{ $message }}</p> @enderror
    </div>
    <div>
        <label class="field-label" for="phone">الهاتف</label>
        <input id="phone" name="phone" value="{{ old('phone', $adminUser->phone) }}" class="form-input">
    </div>
    <div>
        <label class="field-label" for="job_title">المسمى الوظيفي</label>
        <input id="job_title" name="job_title" value="{{ old('job_title', $adminUser->job_title) }}" class="form-input">
    </div>
    <div>
        <label class="field-label" for="locale">اللغة</label>
        <select id="locale" name="locale" class="form-select">
            <option value="ar" @selected(old('locale', $adminUser->locale ?? 'ar') === 'ar')>العربية</option>
            <option value="en" @selected(old('locale', $adminUser->locale) === 'en')>English</option>
        </select>
    </div>
    <div>
        <label class="field-label" for="role_names">الأدوار</label>
        <select id="role_names" name="role_names[]" class="form-select" multiple size="4">
            @php($selectedRoles = old('role_names', $adminUser->exists ? $adminUser->roles->pluck('name')->all() : []))
            @foreach ($roles as $role)
                <option value="{{ $role->name }}" @selected(in_array($role->name, $selectedRoles, true))>{{ $role->name }}</option>
            @endforeach
        </select>
        @error('role_names') <p class="field-help text-[var(--color-danger)]">{{ $message }}</p> @enderror
    </div>
    <div>
        <label class="field-label" for="password">كلمة المرور</label>
        <input id="password" type="password" name="password" class="form-input" {{ $adminUser->exists ? '' : 'required' }}>
    </div>
    <div>
        <label class="field-label" for="password_confirmation">تأكيد كلمة المرور</label>
        <input id="password_confirmation" type="password" name="password_confirmation" class="form-input" {{ $adminUser->exists ? '' : 'required' }}>
    </div>
</div>

<label class="mt-5 flex items-center gap-3 text-sm font-medium text-[var(--color-ink-700)]">
    <input type="checkbox" name="is_active" value="1" class="h-4 w-4 rounded" @checked(old('is_active', $adminUser->is_active ?? true))>
    الحساب نشط
</label>

<div class="mt-6 flex flex-wrap gap-3">
    <button type="submit" class="btn-primary">حفظ</button>
    <a href="{{ route('admin.admins.index') }}" class="btn-secondary">إلغاء</a>
</div>
