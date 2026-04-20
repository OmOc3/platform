<x-layouts.admin :title="$thread->title" :heading="$thread->title" subheading="عرض الرسائل بالتسلسل وتحديث الحالة أو إضافة رد من فريق الإدارة.">
    <section class="grid gap-6 xl:grid-cols-[1.2fr_0.8fr]">
        <section class="panel-tight">
            <div class="space-y-4">
                @foreach ($thread->messages as $message)
                    <article class="surface-outline surface-outline--brand rounded-[1.6rem] px-5 py-5">
                        <div class="flex flex-wrap items-center gap-3">
                            <x-admin.status-badge :label="$message->is_staff_reply ? 'رد إداري' : 'رسالة طالب'" :tone="$message->is_staff_reply ? 'success' : 'neutral'" />
                            <span class="text-xs text-[var(--color-ink-500)]">{{ $message->author?->name ?? 'غير معروف' }}</span>
                            <span class="text-xs text-[var(--color-ink-500)]">{{ $message->created_at->diffForHumans() }}</span>
                        </div>
                        <p class="mt-4 text-sm leading-8 text-[var(--color-ink-700)]">{{ $message->body }}</p>
                        @if ($message->attachments->isNotEmpty())
                            <div class="mt-4 flex flex-wrap gap-2">
                                @foreach ($message->attachments as $attachment)
                                    <span class="status-pill status-pill--brand">{{ $attachment->original_name }}</span>
                                @endforeach
                            </div>
                        @endif
                    </article>
                @endforeach
            </div>
        </section>

        <aside class="space-y-6">
            <section class="panel-tight">
                <p class="text-sm font-semibold text-[var(--color-brand-700)]">تحديث الحالة</p>
                <form method="POST" action="{{ route('admin.forum-threads.update', $thread) }}" class="mt-5 space-y-4">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="field-label" for="status">الحالة</label>
                        <select id="status" name="status" class="form-select" required>
                            @foreach ($statuses as $status)
                                <option value="{{ $status->value }}" @selected(old('status', $thread->status->value) === $status->value)>{{ $status->value }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="field-label" for="visibility">الظهور</label>
                        <select id="visibility" name="visibility" class="form-select" required>
                            @foreach ($visibilities as $visibility)
                                <option value="{{ $visibility->value }}" @selected(old('visibility', $thread->visibility->value) === $visibility->value)>{{ $visibility->value }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button class="btn-secondary">حفظ الحالة</button>
                </form>
            </section>

            <section class="panel-tight">
                <p class="text-sm font-semibold text-[var(--color-brand-700)]">رد إداري</p>
                <form method="POST" action="{{ route('admin.forum-threads.reply', $thread) }}" enctype="multipart/form-data" class="mt-5 space-y-4">
                    @csrf
                    <div>
                        <label class="field-label" for="body">نص الرد</label>
                        <textarea id="body" name="body" class="form-textarea" required>{{ old('body') }}</textarea>
                    </div>
                    <div>
                        <label class="field-label" for="attachments">مرفقات</label>
                        <input id="attachments" type="file" name="attachments[]" multiple class="form-input">
                    </div>
                    <button class="btn-primary">إرسال الرد</button>
                </form>
            </section>
        </aside>
    </section>
</x-layouts.admin>
