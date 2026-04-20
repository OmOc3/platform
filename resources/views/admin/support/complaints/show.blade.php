<x-layouts.admin :title="'متابعة '.$complaint->type->label()" heading="تفاصيل الشكوى / الاقتراح" subheading="مراجعة الرسالة الأصلية، بيانات الطالب، وتحديث حالة المتابعة الإدارية.">
    <section class="grid gap-6 xl:grid-cols-[1fr_0.92fr]">
        <section class="space-y-6">
            <section class="panel-tight">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-semibold text-[var(--color-brand-700)]">{{ $complaint->type->label() }}</p>
                        <h2 class="mt-2 text-2xl font-bold">{{ $complaint->student?->name ?? 'طالب غير معروف' }}</h2>
                        <p class="mt-2 text-sm text-[var(--color-ink-700)]">{{ $complaint->student?->student_number ?? '—' }} / {{ optional($complaint->created_at)->format('Y-m-d H:i') }}</p>
                    </div>
                    <x-admin.status-badge :label="$complaint->status->label()" :tone="$complaint->status->tone()" />
                </div>

                <div class="mt-6 rounded-[1.8rem] bg-[var(--color-panel-muted)] p-5">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-ink-500)]">نص الرسالة</p>
                    <p class="mt-4 text-sm leading-8 text-[var(--color-ink-900)]">{{ $complaint->content }}</p>
                </div>
            </section>

            <section class="panel-tight">
                <p class="text-sm font-semibold text-[var(--color-brand-700)]">بيانات الطالب</p>
                <div class="mt-5 grid gap-4 md:grid-cols-2">
                    <div class="admin-workflow-card">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-ink-500)]">التواصل</p>
                        <p class="mt-3 font-semibold">{{ $complaint->student?->phone ?: '—' }}</p>
                        <p class="mt-1 text-sm text-[var(--color-ink-600)]">{{ $complaint->student?->parent_phone ?: '—' }}</p>
                    </div>
                    <div class="admin-workflow-card">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-ink-500)]">المتابع</p>
                        <p class="mt-3 font-semibold">{{ $complaint->student?->ownerAdmin?->name ?: 'بدون تعيين' }}</p>
                        <p class="mt-1 text-sm text-[var(--color-ink-600)]">{{ $complaint->student?->center?->name_ar ?: 'بدون سنتر' }} / {{ $complaint->student?->group?->name_ar ?: 'بدون مجموعة' }}</p>
                    </div>
                </div>
            </section>
        </section>

        <aside class="space-y-6">
            <section class="panel-tight">
                <p class="text-sm font-semibold text-[var(--color-brand-700)]">تحديث الحالة</p>
                <form method="POST" action="{{ route('admin.complaints.update', $complaint) }}" class="mt-5 space-y-4">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="field-label" for="status">الحالة</label>
                        <select id="status" name="status" class="form-select" required>
                            @foreach ($statuses as $status)
                                <option value="{{ $status->value }}" @selected(old('status', $complaint->status->value) === $status->value)>{{ $status->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="field-label" for="admin_notes">ملاحظات الإدارة</label>
                        <textarea id="admin_notes" name="admin_notes" class="form-textarea">{{ old('admin_notes', $complaint->admin_notes) }}</textarea>
                    </div>
                    <button class="btn-primary w-full">حفظ التحديث</button>
                </form>
            </section>

            <section class="panel-tight">
                <p class="text-sm font-semibold text-[var(--color-brand-700)]">مؤشرات التشغيل</p>
                <ul class="mt-4 space-y-3 text-sm leading-7 text-[var(--color-ink-700)]">
                    <li>يفضل تحويل الرسالة إلى "قيد المتابعة" بمجرد بدء المعالجة حتى تظهر بوضوح في قائمة الفريق.</li>
                    <li>عند اختيار "تم حلها" سيحفظ النظام تاريخ الإغلاق لأول مرة تلقائيًا.</li>
                    <li>يمكن استخدام "مغلقة" للحالات التي لا تحتاج متابعة إضافية بعد الرد أو التوضيح.</li>
                </ul>
            </section>
        </aside>
    </section>
</x-layouts.admin>
