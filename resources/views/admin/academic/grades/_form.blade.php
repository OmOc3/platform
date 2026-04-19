@csrf
@if($grade->exists)
    @method('PUT')
@endif

<div class="grid gap-5 md:grid-cols-2">
    <div>
        <label class="field-label" for="name_ar">الاسم العربي</label>
        <input id="name_ar" name="name_ar" value="{{ old('name_ar', $grade->name_ar) }}" class="form-input" required>
    </div>
    <div>
        <label class="field-label" for="name_en">الاسم الإنجليزي</label>
        <input id="name_en" name="name_en" value="{{ old('name_en', $grade->name_en) }}" class="form-input">
    </div>
    <div>
        <label class="field-label" for="code">الكود</label>
        <input id="code" name="code" value="{{ old('code', $grade->code) }}" class="form-input" required>
    </div>
    <div>
        <label class="field-label" for="sort_order">الترتيب</label>
        <input id="sort_order" type="number" min="0" name="sort_order" value="{{ old('sort_order', $grade->sort_order ?? 0) }}" class="form-input">
    </div>
</div>

<label class="mt-5 flex items-center gap-3 text-sm font-medium text-[var(--color-ink-700)]">
    <input type="checkbox" name="is_active" value="1" class="h-4 w-4 rounded" @checked(old('is_active', $grade->is_active ?? true))>
    الصف نشط
</label>

<div class="mt-6 flex flex-wrap gap-3">
    <button type="submit" class="btn-primary">حفظ</button>
    <a href="{{ route('admin.grades.index') }}" class="btn-secondary">إلغاء</a>
</div>
