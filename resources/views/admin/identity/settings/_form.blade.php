@csrf
@if($setting->exists)
    @method('PUT')
@endif

<div class="grid gap-5 md:grid-cols-2">
    <div>
        <label class="field-label" for="group">المجموعة</label>
        <input id="group" name="group" value="{{ old('group', $setting->group) }}" class="form-input" required>
    </div>
    <div>
        <label class="field-label" for="key">المفتاح</label>
        <input id="key" name="key" value="{{ old('key', $setting->key) }}" class="form-input" required>
    </div>
    <div>
        <label class="field-label" for="label">العنوان</label>
        <input id="label" name="label" value="{{ old('label', $setting->label) }}" class="form-input" required>
    </div>
    <div>
        <label class="field-label" for="type">النوع</label>
        <select id="type" name="type" class="form-select">
            @foreach ($settingTypes as $type)
                <option value="{{ $type->value }}" @selected(old('type', $setting->type?->value ?? $setting->type ?? 'string') === $type->value)>{{ $type->value }}</option>
            @endforeach
        </select>
    </div>
    <div class="md:col-span-2">
        <label class="field-label" for="description">الوصف</label>
        <textarea id="description" name="description" class="form-textarea">{{ old('description', $setting->description) }}</textarea>
    </div>
    <div class="md:col-span-2">
        <label class="field-label" for="value">القيمة</label>
        <textarea id="value" name="value" class="form-textarea">{{ old('value', $setting->value) }}</textarea>
    </div>
</div>

<label class="mt-5 flex items-center gap-3 text-sm font-medium text-[var(--color-ink-700)]">
    <input type="checkbox" name="is_public" value="1" class="h-4 w-4 rounded" @checked(old('is_public', $setting->is_public ?? false))>
    متاح للعرض العام
</label>

<div class="mt-6 flex flex-wrap gap-3">
    <button type="submit" class="btn-primary">حفظ</button>
    <a href="{{ route('admin.settings.index') }}" class="btn-secondary">إلغاء</a>
</div>
