@csrf
@if($lecture->exists)
    @method('PUT')
@endif

@php
    $assetRows = old('assets', $lecture->assets->map(fn ($asset) => [
        'kind' => $asset->kind->value,
        'title' => $asset->title,
        'url' => $asset->url,
        'body' => $asset->body,
        'sort_order' => $asset->sort_order,
        'is_active' => $asset->is_active,
    ])->all());
    $checkpointRows = old('checkpoints', $lecture->checkpoints->map(fn ($checkpoint) => [
        'title' => $checkpoint->title,
        'position_seconds' => $checkpoint->position_seconds,
        'sort_order' => $checkpoint->sort_order,
        'is_required' => $checkpoint->is_required,
    ])->all());
    $completionThreshold = old('completion_threshold_percent', data_get($lecture->metadata, 'completion_threshold_percent', 90));
@endphp

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
    <div>
        <label class="field-label" for="completion_threshold_percent">نسبة الاكتمال المطلوبة</label>
        <input id="completion_threshold_percent" type="number" min="1" max="100" name="completion_threshold_percent" value="{{ $completionThreshold }}" class="form-input">
        <p class="field-help">تُستخدم لتحديد متى تُعتبر المحاضرة مكتملة تلقائيًا داخل سجل الطالب.</p>
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

<section class="mt-8 space-y-6 rounded-[2rem] border border-[var(--color-border-soft)] bg-[var(--color-panel-muted)] p-5" data-dynamic-collection="assets">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h3 class="text-lg font-bold">أصول تسليم المحاضرة</h3>
            <p class="mt-2 text-sm leading-7 text-[var(--color-ink-700)]">يدعم الإصدار الحالي الروابط الخارجية والنصوص والموارد المساندة فقط.</p>
        </div>
        <button type="button" class="btn-secondary" data-add-row="assets">إضافة أصل</button>
    </div>

    <div class="space-y-4" data-rows="assets">
        @forelse ($assetRows as $index => $asset)
            <div class="surface-card rounded-[1.8rem] p-4" data-row>
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="field-label">النوع</label>
                        <select name="assets[{{ $index }}][kind]" class="form-select">
                            @foreach ($assetKinds as $assetKind)
                                <option value="{{ $assetKind->value }}" @selected(($asset['kind'] ?? null) === $assetKind->value)>{{ $assetKind->value }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="field-label">العنوان</label>
                        <input name="assets[{{ $index }}][title]" value="{{ $asset['title'] ?? '' }}" class="form-input">
                    </div>
                    <div>
                        <label class="field-label">الرابط</label>
                        <input name="assets[{{ $index }}][url]" value="{{ $asset['url'] ?? '' }}" class="form-input">
                    </div>
                    <div>
                        <label class="field-label">الترتيب</label>
                        <input type="number" min="0" name="assets[{{ $index }}][sort_order]" value="{{ $asset['sort_order'] ?? $index + 1 }}" class="form-input">
                    </div>
                    <div class="md:col-span-2">
                        <label class="field-label">النص / الملاحظات</label>
                        <textarea name="assets[{{ $index }}][body]" class="form-textarea">{{ $asset['body'] ?? '' }}</textarea>
                    </div>
                </div>
                <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
                    <label class="flex items-center gap-3 text-sm font-medium text-[var(--color-ink-700)]">
                        <input type="checkbox" name="assets[{{ $index }}][is_active]" value="1" class="h-4 w-4 rounded" @checked(($asset['is_active'] ?? true))>
                        نشط داخل صفحة الطالب
                    </label>
                    <button type="button" class="btn-danger" data-remove-row>حذف الأصل</button>
                </div>
            </div>
        @empty
            <p class="text-sm text-[var(--color-ink-500)]" data-empty-state>لم تتم إضافة أصول تسليم بعد.</p>
        @endforelse
    </div>
</section>

<section class="mt-8 space-y-6 rounded-[2rem] border border-[var(--color-border-soft)] bg-[var(--color-panel-muted)] p-5" data-dynamic-collection="checkpoints">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h3 class="text-lg font-bold">النقاط المرحلية</h3>
            <p class="mt-2 text-sm leading-7 text-[var(--color-ink-700)]">يمكن للطالب تسجيل الوصول إلى هذه المحطات لتنعكس على نسبة التقدم.</p>
        </div>
        <button type="button" class="btn-secondary" data-add-row="checkpoints">إضافة نقطة</button>
    </div>

    <div class="space-y-4" data-rows="checkpoints">
        @forelse ($checkpointRows as $index => $checkpoint)
            <div class="surface-card rounded-[1.8rem] p-4" data-row>
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="field-label">العنوان</label>
                        <input name="checkpoints[{{ $index }}][title]" value="{{ $checkpoint['title'] ?? '' }}" class="form-input">
                    </div>
                    <div>
                        <label class="field-label">الموضع بالثواني</label>
                        <input type="number" min="0" name="checkpoints[{{ $index }}][position_seconds]" value="{{ $checkpoint['position_seconds'] ?? '' }}" class="form-input">
                    </div>
                    <div>
                        <label class="field-label">الترتيب</label>
                        <input type="number" min="0" name="checkpoints[{{ $index }}][sort_order]" value="{{ $checkpoint['sort_order'] ?? $index + 1 }}" class="form-input">
                    </div>
                    <div class="flex items-center">
                        <label class="mt-6 flex items-center gap-3 text-sm font-medium text-[var(--color-ink-700)]">
                            <input type="checkbox" name="checkpoints[{{ $index }}][is_required]" value="1" class="h-4 w-4 rounded" @checked(($checkpoint['is_required'] ?? true))>
                            نقطة مطلوبة
                        </label>
                    </div>
                </div>
                <div class="mt-4 flex justify-end">
                    <button type="button" class="btn-danger" data-remove-row>حذف النقطة</button>
                </div>
            </div>
        @empty
            <p class="text-sm text-[var(--color-ink-500)]" data-empty-state>لا توجد نقاط مرحلية بعد.</p>
        @endforelse
    </div>
</section>

<div class="mt-6 flex flex-wrap gap-3">
    <button type="submit" class="btn-primary">حفظ</button>
    <a href="{{ route('admin.lectures.index') }}" class="btn-secondary">إلغاء</a>
</div>

<template id="lecture-asset-row-template">
    <div class="surface-card rounded-[1.8rem] p-4" data-row>
        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label class="field-label">النوع</label>
                <select class="form-select" data-field="kind">
                    @foreach ($assetKinds as $assetKind)
                        <option value="{{ $assetKind->value }}">{{ $assetKind->value }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="field-label">العنوان</label>
                <input class="form-input" data-field="title">
            </div>
            <div>
                <label class="field-label">الرابط</label>
                <input class="form-input" data-field="url">
            </div>
            <div>
                <label class="field-label">الترتيب</label>
                <input type="number" min="0" class="form-input" data-field="sort_order">
            </div>
            <div class="md:col-span-2">
                <label class="field-label">النص / الملاحظات</label>
                <textarea class="form-textarea" data-field="body"></textarea>
            </div>
        </div>
        <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
            <label class="flex items-center gap-3 text-sm font-medium text-[var(--color-ink-700)]">
                <input type="checkbox" value="1" class="h-4 w-4 rounded" data-field="is_active" checked>
                نشط داخل صفحة الطالب
            </label>
            <button type="button" class="btn-danger" data-remove-row>حذف الأصل</button>
        </div>
    </div>
</template>

<template id="lecture-checkpoint-row-template">
    <div class="surface-card rounded-[1.8rem] p-4" data-row>
        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label class="field-label">العنوان</label>
                <input class="form-input" data-field="title">
            </div>
            <div>
                <label class="field-label">الموضع بالثواني</label>
                <input type="number" min="0" class="form-input" data-field="position_seconds">
            </div>
            <div>
                <label class="field-label">الترتيب</label>
                <input type="number" min="0" class="form-input" data-field="sort_order">
            </div>
            <div class="flex items-center">
                <label class="mt-6 flex items-center gap-3 text-sm font-medium text-[var(--color-ink-700)]">
                    <input type="checkbox" value="1" class="h-4 w-4 rounded" data-field="is_required" checked>
                    نقطة مطلوبة
                </label>
            </div>
        </div>
        <div class="mt-4 flex justify-end">
            <button type="button" class="btn-danger" data-remove-row>حذف النقطة</button>
        </div>
    </div>
</template>

@once
    @push('scripts')
        <script>
            document.querySelectorAll('[data-dynamic-collection]').forEach((section) => {
                const type = section.dataset.dynamicCollection;
                const rowsHost = section.querySelector(`[data-rows="${type}"]`);
                const addButton = section.querySelector(`[data-add-row="${type}"]`);
                const template = document.getElementById(type === 'assets' ? 'lecture-asset-row-template' : 'lecture-checkpoint-row-template');

                const refreshState = () => {
                    const empty = rowsHost.querySelector('[data-empty-state]');
                    const hasRows = rowsHost.querySelectorAll('[data-row]').length > 0;

                    if (empty) {
                        empty.style.display = hasRows ? 'none' : '';
                    }
                };

                const nextIndex = () => rowsHost.querySelectorAll('[data-row]').length;

                const bindRemove = (row) => {
                    row.querySelector('[data-remove-row]')?.addEventListener('click', () => {
                        row.remove();
                        refreshState();
                    });
                };

                rowsHost.querySelectorAll('[data-row]').forEach(bindRemove);
                refreshState();

                addButton?.addEventListener('click', () => {
                    const index = nextIndex();
                    const clone = template.content.firstElementChild.cloneNode(true);

                    clone.querySelectorAll('[data-field]').forEach((field) => {
                        const name = field.dataset.field;
                        field.name = `${type}[${index}][${name}]`;

                        if (field.type === 'checkbox') {
                            field.checked = true;
                        } else if (name === 'sort_order') {
                            field.value = index + 1;
                        } else {
                            field.value = '';
                        }
                    });

                    bindRemove(clone);
                    rowsHost.appendChild(clone);
                    refreshState();
                });
            });
        </script>
    @endpush
@endonce
