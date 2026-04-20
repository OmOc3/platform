@csrf
@method('PUT')

<div class="grid gap-5 md:grid-cols-2">
    <div class="md:col-span-2">
        <label class="field-label" for="name">الاسم</label>
        <input id="name" name="name" value="{{ old('name', $student->name) }}" class="form-input" required>
        @error('name') <p class="field-help text-[var(--color-danger)]">{{ $message }}</p> @enderror
    </div>
    <div>
        <label class="field-label" for="email">البريد الإلكتروني</label>
        <input id="email" type="email" name="email" value="{{ old('email', $student->email) }}" class="form-input" required>
        @error('email') <p class="field-help text-[var(--color-danger)]">{{ $message }}</p> @enderror
    </div>
    <div>
        <label class="field-label" for="phone">الهاتف</label>
        <input id="phone" name="phone" value="{{ old('phone', $student->phone) }}" class="form-input" required>
        @error('phone') <p class="field-help text-[var(--color-danger)]">{{ $message }}</p> @enderror
    </div>
    <div>
        <label class="field-label" for="password">كلمة المرور الجديدة</label>
        <input id="password" type="password" name="password" class="form-input" autocomplete="new-password" placeholder="اتركها فارغة إذا لا تريد تغييرها">
        <p class="field-help">يستطيع الأدمن تعيين كلمة مرور جديدة للطالب من هنا عند الحاجة.</p>
        @error('password') <p class="field-help text-[var(--color-danger)]">{{ $message }}</p> @enderror
    </div>
    <div>
        <label class="field-label" for="password_confirmation">تأكيد كلمة المرور</label>
        <input id="password_confirmation" type="password" name="password_confirmation" class="form-input" autocomplete="new-password">
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
    <div>
        <label class="field-label" for="status">الحالة</label>
        <select id="status" name="status" class="form-select" required>
            @foreach ($statuses as $status)
                <option value="{{ $status->value }}" @selected(old('status', $student->status->value) === $status->value)>{{ $status->label() }}</option>
            @endforeach
        </select>
        @error('status') <p class="field-help text-[var(--color-danger)]">{{ $message }}</p> @enderror
    </div>
    <div>
        <label class="field-label" for="status_reason">سبب تغيير الحالة</label>
        <input id="status_reason" name="status_reason" value="{{ old('status_reason') }}" class="form-input" placeholder="ملاحظة قصيرة تحفظ في سجل الحالة">
        @error('status_reason') <p class="field-help text-[var(--color-danger)]">{{ $message }}</p> @enderror
    </div>
    <div>
        <label class="field-label" for="source_type">المصدر</label>
        <select id="source_type" name="source_type" class="form-select" required>
            @foreach ($sourceTypes as $sourceType)
                <option value="{{ $sourceType->value }}" @selected(old('source_type', $student->source_type?->value) === $sourceType->value)>{{ $sourceType->label() }}</option>
            @endforeach
        </select>
        @error('source_type') <p class="field-help text-[var(--color-danger)]">{{ $message }}</p> @enderror
    </div>
    <div>
        <label class="field-label" for="owner_admin_id">المتابع الإداري</label>
        <select id="owner_admin_id" name="owner_admin_id" class="form-select">
            <option value="">بدون تعيين</option>
            @foreach ($owners as $owner)
                <option value="{{ $owner->id }}" @selected((string) old('owner_admin_id', $student->owner_admin_id) === (string) $owner->id)>{{ $owner->name }}</option>
            @endforeach
        </select>
        @error('owner_admin_id') <p class="field-help text-[var(--color-danger)]">{{ $message }}</p> @enderror
    </div>
    <div>
        <label class="field-label" for="grade_id">الصف</label>
        <select id="grade_id" name="grade_id" class="form-select">
            <option value="">بدون تحديد</option>
            @foreach ($grades as $grade)
                <option value="{{ $grade->id }}" @selected((string) old('grade_id', $student->grade_id) === (string) $grade->id)>{{ $grade->name_ar }}</option>
            @endforeach
        </select>
        @error('grade_id') <p class="field-help text-[var(--color-danger)]">{{ $message }}</p> @enderror
    </div>
    <div>
        <label class="field-label" for="track_id">المسار</label>
        <select id="track_id" name="track_id" class="form-select">
            <option value="">بدون تحديد</option>
            @foreach ($tracks as $track)
                <option value="{{ $track->id }}" @selected((string) old('track_id', $student->track_id) === (string) $track->id)>{{ $track->name_ar }}</option>
            @endforeach
        </select>
        @error('track_id') <p class="field-help text-[var(--color-danger)]">{{ $message }}</p> @enderror
    </div>
    <div>
        <label class="field-label" for="center_id">السنتر</label>
        <select id="center_id" name="center_id" class="form-select">
            <option value="">بدون تحديد</option>
            @foreach ($centers as $center)
                <option value="{{ $center->id }}" @selected((string) old('center_id', $student->center_id) === (string) $center->id)>{{ $center->name_ar }}</option>
            @endforeach
        </select>
        @error('center_id') <p class="field-help text-[var(--color-danger)]">{{ $message }}</p> @enderror
    </div>
    <div>
        <label class="field-label" for="group_id">المجموعة</label>
        <select id="group_id" name="group_id" class="form-select">
            <option value="">بدون تحديد</option>
            @foreach ($groups as $group)
                <option value="{{ $group->id }}" @selected((string) old('group_id', $student->group_id) === (string) $group->id)>{{ $group->name_ar }}</option>
            @endforeach
        </select>
        @error('group_id') <p class="field-help text-[var(--color-danger)]">{{ $message }}</p> @enderror
    </div>
    <div class="md:col-span-2 flex items-center gap-3">
        <input id="is_azhar" type="checkbox" name="is_azhar" value="1" class="h-4 w-4 rounded border-[var(--color-brand-200)] text-[var(--color-brand-700)]" @checked(old('is_azhar', $student->is_azhar))>
        <label for="is_azhar" class="text-sm text-[var(--color-ink-700)]">طالب أزهري</label>
    </div>
    <div class="md:col-span-2">
        <label class="field-label" for="notes">ملاحظات المتابعة</label>
        <textarea id="notes" name="notes" class="form-textarea">{{ old('notes', $student->notes) }}</textarea>
        @error('notes') <p class="field-help text-[var(--color-danger)]">{{ $message }}</p> @enderror
    </div>
</div>

<div class="mt-6 flex gap-3">
    <button type="submit" class="btn-primary">حفظ التحديثات</button>
    <a href="{{ route('admin.students.index') }}" class="btn-secondary">العودة للقائمة</a>
</div>
