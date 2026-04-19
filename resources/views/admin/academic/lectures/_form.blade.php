@csrf
@if($lecture->exists)
    @method('PUT')
@endif

<div class="grid gap-5 md:grid-cols-2">
    <div>
        <label class="field-label" for="title">العنوان</label>
        <input id="title" name="title" value="{{ old('title', $lecture->title) }}" class="form-input" required>
    </div>
    <div>
        <label class="field-label" for="slug">الرابط المختصر</label>
        <input id="slug" name="slug" value="{{ old('slug', $lecture->slug) }}" class="form-input" required>
    </div>
    <div>
        <label class="field-label" for="grade_id">الصف</label>
        <select id="grade_id" name="grade_id" class="form-select" required>
            <option value="">اختر الصف</option>
            @foreach ($grades as $grade)
                <option value="{{ $grade->id }}" @selected((string) old('grade_id', $lecture->grade_id) === (string) $grade->id)>{{ $grade->name_ar }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="field-label" for="track_id">المسار</label>
        <select id="track_id" name="track_id" class="form-select">
            <option value="">عام</option>
            @foreach ($tracks as $track)
                <option value="{{ $track->id }}" @selected((string) old('track_id', $lecture->track_id) === (string) $track->id)>{{ $track->name_ar }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="field-label" for="curriculum_section_id">قسم المنهج</label>
        <select id="curriculum_section_id" name="curriculum_section_id" class="form-select">
            <option value="">غير مرتبط</option>
            @foreach ($curriculumSections as $section)
                <option value="{{ $section->id }}" @selected((string) old('curriculum_section_id', $lecture->curriculum_section_id) === (string) $section->id)>{{ $section->name_ar }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="field-label" for="lecture_section_id">قسم المحاضرات</label>
        <select id="lecture_section_id" name="lecture_section_id" class="form-select">
            <option value="">غير مرتبط</option>
            @foreach ($lectureSections as $section)
                <option value="{{ $section->id }}" @selected((string) old('lecture_section_id', $lecture->lecture_section_id) === (string) $section->id)>{{ $section->name_ar }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="field-label" for="type">النوع</label>
        <select id="type" name="type" class="form-select" required>
            @foreach ($types as $type)
                <option value="{{ $type->value }}" @selected(old('type', $lecture->type?->value ?? $lecture->type) === $type->value)>{{ $type->value }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="field-label" for="thumbnail_url">رابط الصورة</label>
        <input id="thumbnail_url" name="thumbnail_url" value="{{ old('thumbnail_url', $lecture->thumbnail_url) }}" class="form-input">
    </div>
    <div>
        <label class="field-label" for="price_amount">السعر</label>
        <input id="price_amount" type="number" min="0" name="price_amount" value="{{ old('price_amount', $lecture->price_amount ?? 0) }}" class="form-input" required>
    </div>
    <div>
        <label class="field-label" for="duration_minutes">المدة بالدقائق</label>
        <input id="duration_minutes" type="number" min="1" name="duration_minutes" value="{{ old('duration_minutes', $lecture->duration_minutes) }}" class="form-input">
    </div>
    <div>
        <label class="field-label" for="currency">العملة</label>
        <input id="currency" name="currency" value="{{ old('currency', $lecture->currency ?? 'EGP') }}" class="form-input" required>
    </div>
    <div>
        <label class="field-label" for="published_at">تاريخ النشر</label>
        <input id="published_at" type="datetime-local" name="published_at" value="{{ old('published_at', optional($lecture->published_at)->format('Y-m-d\TH:i')) }}" class="form-input">
    </div>
    <div>
        <label class="field-label" for="sort_order">الترتيب</label>
        <input id="sort_order" type="number" min="0" name="sort_order" value="{{ old('sort_order', $lecture->sort_order ?? 0) }}" class="form-input">
    </div>
    <div class="md:col-span-2">
        <label class="field-label" for="short_description">الوصف المختصر</label>
        <input id="short_description" name="short_description" value="{{ old('short_description', $lecture->short_description) }}" class="form-input">
    </div>
    <div class="md:col-span-2">
        <label class="field-label" for="long_description">الوصف التفصيلي</label>
        <textarea id="long_description" name="long_description" class="form-textarea">{{ old('long_description', $lecture->long_description) }}</textarea>
    </div>
</div>

<div class="mt-5 grid gap-3 md:grid-cols-3">
    <label class="flex items-center gap-3 text-sm font-medium text-[var(--color-ink-700)]">
        <input type="checkbox" name="is_active" value="1" class="h-4 w-4 rounded" @checked(old('is_active', $lecture->is_active ?? true))>
        العنصر نشط
    </label>
    <label class="flex items-center gap-3 text-sm font-medium text-[var(--color-ink-700)]">
        <input type="checkbox" name="is_featured" value="1" class="h-4 w-4 rounded" @checked(old('is_featured', $lecture->is_featured ?? false))>
        عنصر مميز
    </label>
    <label class="flex items-center gap-3 text-sm font-medium text-[var(--color-ink-700)]">
        <input type="checkbox" name="is_free" value="1" class="h-4 w-4 rounded" @checked(old('is_free', $lecture->is_free ?? false))>
        مجاني
    </label>
</div>

<div class="mt-6 flex flex-wrap gap-3">
    <button type="submit" class="btn-primary">حفظ</button>
    <a href="{{ route('admin.lectures.index') }}" class="btn-secondary">إلغاء</a>
</div>
