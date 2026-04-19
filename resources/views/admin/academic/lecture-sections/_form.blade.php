@csrf
@if($section->exists)
    @method('PUT')
@endif

<div class="grid gap-5 md:grid-cols-2">
    <div>
        <label class="field-label" for="grade_id">الصف</label>
        <select id="grade_id" name="grade_id" class="form-select" required>
            <option value="">اختر الصف</option>
            @foreach ($grades as $grade)
                <option value="{{ $grade->id }}" @selected((string) old('grade_id', $section->grade_id) === (string) $grade->id)>{{ $grade->name_ar }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="field-label" for="track_id">المسار</label>
        <select id="track_id" name="track_id" class="form-select">
            <option value="">عام</option>
            @foreach ($tracks as $track)
                <option value="{{ $track->id }}" @selected((string) old('track_id', $section->track_id) === (string) $track->id)>{{ $track->name_ar }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="field-label" for="curriculum_section_id">قسم المنهج</label>
        <select id="curriculum_section_id" name="curriculum_section_id" class="form-select">
            <option value="">غير مرتبط</option>
            @foreach ($curriculumSections as $curriculumSection)
                <option value="{{ $curriculumSection->id }}" @selected((string) old('curriculum_section_id', $section->curriculum_section_id) === (string) $curriculumSection->id)>{{ $curriculumSection->name_ar }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="field-label" for="slug">الرابط المختصر</label>
        <input id="slug" name="slug" value="{{ old('slug', $section->slug) }}" class="form-input" required>
    </div>
    <div>
        <label class="field-label" for="name_ar">الاسم العربي</label>
        <input id="name_ar" name="name_ar" value="{{ old('name_ar', $section->name_ar) }}" class="form-input" required>
    </div>
    <div>
        <label class="field-label" for="name_en">الاسم الإنجليزي</label>
        <input id="name_en" name="name_en" value="{{ old('name_en', $section->name_en) }}" class="form-input">
    </div>
    <div>
        <label class="field-label" for="sort_order">الترتيب</label>
        <input id="sort_order" type="number" min="0" name="sort_order" value="{{ old('sort_order', $section->sort_order ?? 0) }}" class="form-input">
    </div>
    <div class="md:col-span-2">
        <label class="field-label" for="description">الوصف</label>
        <textarea id="description" name="description" class="form-textarea">{{ old('description', $section->description) }}</textarea>
    </div>
</div>

<label class="mt-5 flex items-center gap-3 text-sm font-medium text-[var(--color-ink-700)]">
    <input type="checkbox" name="is_active" value="1" class="h-4 w-4 rounded" @checked(old('is_active', $section->is_active ?? true))>
    القسم نشط
</label>

<div class="mt-6 flex flex-wrap gap-3">
    <button type="submit" class="btn-primary">حفظ</button>
    <a href="{{ route('admin.lecture-sections.index') }}" class="btn-secondary">إلغاء</a>
</div>
