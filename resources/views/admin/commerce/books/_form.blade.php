@csrf
@if($book->exists)
    @method('PUT')
@endif

<div class="grid gap-5 md:grid-cols-2">
    <div>
        <label class="field-label" for="name_ar">اسم الكتاب</label>
        <input id="name_ar" name="name_ar" value="{{ old('name_ar', $book->product?->name_ar) }}" class="form-input" required>
    </div>
    <div>
        <label class="field-label" for="slug">الرابط المختصر</label>
        <input id="slug" name="slug" value="{{ old('slug', $book->product?->slug) }}" class="form-input" required>
    </div>
    <div>
        <label class="field-label" for="author_name">المؤلف</label>
        <input id="author_name" name="author_name" value="{{ old('author_name', $book->author_name) }}" class="form-input">
    </div>
    <div>
        <label class="field-label" for="page_count">عدد الصفحات</label>
        <input id="page_count" type="number" min="1" name="page_count" value="{{ old('page_count', $book->page_count) }}" class="form-input">
    </div>
    <div>
        <label class="field-label" for="price_amount">السعر</label>
        <input id="price_amount" type="number" min="0" name="price_amount" value="{{ old('price_amount', $book->product?->price_amount ?? 0) }}" class="form-input" required>
    </div>
    <div>
        <label class="field-label" for="currency">العملة</label>
        <input id="currency" name="currency" value="{{ old('currency', $book->product?->currency ?? 'EGP') }}" class="form-input" required>
    </div>
    <div>
        <label class="field-label" for="stock_quantity">المخزون</label>
        <input id="stock_quantity" type="number" min="0" name="stock_quantity" value="{{ old('stock_quantity', $book->stock_quantity ?? 0) }}" class="form-input" required>
    </div>
    <div>
        <label class="field-label" for="availability_status">الحالة</label>
        <select id="availability_status" name="availability_status" class="form-select" required>
            @foreach ($availabilities as $availability)
                <option value="{{ $availability->value }}" @selected(old('availability_status', $book->availability_status?->value ?? $book->availability_status) === $availability->value)>{{ $availability->label() }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="field-label" for="cover_badge">شارة الغلاف</label>
        <input id="cover_badge" name="cover_badge" value="{{ old('cover_badge', $book->cover_badge) }}" class="form-input">
    </div>
    <div>
        <label class="field-label" for="thumbnail_url">رابط الصورة</label>
        <input id="thumbnail_url" name="thumbnail_url" value="{{ old('thumbnail_url', $book->product?->thumbnail_url) }}" class="form-input">
    </div>
    <div>
        <label class="field-label" for="published_at">تاريخ النشر</label>
        <input id="published_at" type="datetime-local" name="published_at" value="{{ old('published_at', optional($book->product?->published_at)->format('Y-m-d\TH:i')) }}" class="form-input">
    </div>
    <div class="md:col-span-2">
        <label class="field-label" for="teaser">الوصف المختصر</label>
        <input id="teaser" name="teaser" value="{{ old('teaser', $book->product?->teaser) }}" class="form-input">
    </div>
    <div class="md:col-span-2">
        <label class="field-label" for="description">الوصف التفصيلي</label>
        <textarea id="description" name="description" class="form-textarea">{{ old('description', $book->product?->description) }}</textarea>
    </div>
</div>

<div class="mt-5 grid gap-3 md:grid-cols-2">
    <label class="flex items-center gap-3 text-sm font-medium text-[var(--color-ink-700)]">
        <input type="checkbox" name="is_active" value="1" class="h-4 w-4 rounded" @checked(old('is_active', $book->product?->is_active ?? true))>
        الكتاب نشط
    </label>
    <label class="flex items-center gap-3 text-sm font-medium text-[var(--color-ink-700)]">
        <input type="checkbox" name="is_featured" value="1" class="h-4 w-4 rounded" @checked(old('is_featured', $book->product?->is_featured ?? false))>
        عنصر مميز
    </label>
</div>

<div class="mt-6 flex flex-wrap gap-3">
    <button type="submit" class="btn-primary">حفظ</button>
    <a href="{{ route('admin.books.index') }}" class="btn-secondary">إلغاء</a>
</div>
