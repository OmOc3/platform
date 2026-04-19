@csrf
@if($package->exists)
    @method('PUT')
@endif

<div class="grid gap-5 md:grid-cols-2">
    <div>
        <label class="field-label" for="name_ar">اسم الباقة</label>
        <input id="name_ar" name="name_ar" value="{{ old('name_ar', $package->product?->name_ar) }}" class="form-input" required>
    </div>
    <div>
        <label class="field-label" for="slug">الرابط المختصر</label>
        <input id="slug" name="slug" value="{{ old('slug', $package->product?->slug) }}" class="form-input" required>
    </div>
    <div>
        <label class="field-label" for="billing_cycle_label">وصف الدورة</label>
        <input id="billing_cycle_label" name="billing_cycle_label" value="{{ old('billing_cycle_label', $package->billing_cycle_label) }}" class="form-input">
    </div>
    <div>
        <label class="field-label" for="access_period_days">مدة التفعيل بالأيام</label>
        <input id="access_period_days" type="number" min="1" name="access_period_days" value="{{ old('access_period_days', $package->access_period_days) }}" class="form-input">
    </div>
    <div>
        <label class="field-label" for="price_amount">السعر</label>
        <input id="price_amount" type="number" min="0" name="price_amount" value="{{ old('price_amount', $package->product?->price_amount ?? 0) }}" class="form-input" required>
    </div>
    <div>
        <label class="field-label" for="currency">العملة</label>
        <input id="currency" name="currency" value="{{ old('currency', $package->product?->currency ?? 'EGP') }}" class="form-input" required>
    </div>
    <div class="md:col-span-2">
        <label class="field-label" for="teaser">الوصف المختصر</label>
        <input id="teaser" name="teaser" value="{{ old('teaser', $package->product?->teaser) }}" class="form-input">
    </div>
    <div class="md:col-span-2">
        <label class="field-label" for="description">الوصف التفصيلي</label>
        <textarea id="description" name="description" class="form-textarea">{{ old('description', $package->product?->description) }}</textarea>
    </div>
    <div>
        <label class="field-label" for="thumbnail_url">رابط الصورة</label>
        <input id="thumbnail_url" name="thumbnail_url" value="{{ old('thumbnail_url', $package->product?->thumbnail_url) }}" class="form-input">
    </div>
    <div>
        <label class="field-label" for="published_at">تاريخ النشر</label>
        <input id="published_at" type="datetime-local" name="published_at" value="{{ old('published_at', optional($package->product?->published_at)->format('Y-m-d\TH:i')) }}" class="form-input">
    </div>
    <div>
        <label class="field-label" for="overlap_rule">سياسة التعارض</label>
        <select id="overlap_rule" name="overlap_rule" class="form-select">
            <option value="block" @selected(old('overlap_rule', $package->metadata['overlap_rule'] ?? 'block') === 'block')>منع الشراء عند التداخل</option>
            <option value="allow" @selected(old('overlap_rule', $package->metadata['overlap_rule'] ?? 'block') === 'allow')>السماح مع تنبيه</option>
        </select>
    </div>
    <div class="md:col-span-2">
        <label class="field-label" for="item_ids">العناصر المضمنة</label>
        <select id="item_ids" name="item_ids[]" class="form-select" multiple size="8" required>
            @php($selectedItems = old('item_ids', $package->items?->pluck('item_id')->all() ?? []))
            @foreach ($lectures as $lecture)
                <option value="{{ $lecture->id }}" @selected(in_array($lecture->id, array_map('intval', $selectedItems), true))>{{ $lecture->title }}</option>
            @endforeach
        </select>
        <p class="field-help">اضغط Ctrl أو Command لاختيار أكثر من عنصر.</p>
    </div>
</div>

<div class="mt-5 grid gap-3 md:grid-cols-2">
    <label class="flex items-center gap-3 text-sm font-medium text-[var(--color-ink-700)]">
        <input type="checkbox" name="is_active" value="1" class="h-4 w-4 rounded" @checked(old('is_active', $package->product?->is_active ?? true))>
        الباقة نشطة
    </label>
    <label class="flex items-center gap-3 text-sm font-medium text-[var(--color-ink-700)]">
        <input type="checkbox" name="is_featured" value="1" class="h-4 w-4 rounded" @checked(old('is_featured', $package->is_featured ?? false))>
        باقة مميزة
    </label>
</div>

<div class="mt-6 flex flex-wrap gap-3">
    <button type="submit" class="btn-primary">حفظ</button>
    <a href="{{ route('admin.packages.index') }}" class="btn-secondary">إلغاء</a>
</div>
