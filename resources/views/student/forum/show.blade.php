<x-layouts.student :title="$thread->title" :heading="$thread->title" subheading="تسلسل الرسائل والمرفقات والردود الإدارية المرتبطة بهذا السؤال.">
    <section class="space-y-6">
        <section class="panel-tight">
            <div class="flex flex-wrap items-center gap-3">
                <x-admin.status-badge :label="$thread->status->label()" :tone="$thread->status->tone()" />
                <span class="text-xs text-[var(--color-ink-500)]">{{ $thread->student?->name }}</span>
                <span class="text-xs text-[var(--color-ink-500)]">{{ optional($thread->last_activity_at)->diffForHumans() }}</span>
            </div>

            <div class="mt-6 grid gap-4 md:grid-cols-3">
                <div class="portal-summary-card">
                    <span class="portal-summary-card__label">عدد الرسائل</span>
                    <strong class="portal-summary-card__value">{{ $thread->messages->count() }}</strong>
                </div>
                <div class="portal-summary-card">
                    <span class="portal-summary-card__label">آخر نشاط</span>
                    <strong class="portal-summary-card__value">{{ optional($thread->last_activity_at)->diffForHumans() ?? '—' }}</strong>
                </div>
                <div class="portal-summary-card">
                    <span class="portal-summary-card__label">نوع المتابعة</span>
                    <strong class="portal-summary-card__value">{{ $thread->status->label() }}</strong>
                </div>
            </div>
        </section>

        <section class="space-y-4">
            @foreach ($thread->messages as $message)
                <article @class([
                    'panel-tight',
                    'surface-tone--success' => $message->is_staff_reply,
                ])>
                    <div class="flex flex-wrap items-center gap-3">
                        <x-admin.status-badge :label="$message->is_staff_reply ? 'رد إداري' : 'رسالة طالب'" :tone="$message->is_staff_reply ? 'success' : 'neutral'" />
                        <span class="text-xs text-[var(--color-ink-500)]">{{ $message->author?->name ?? 'غير معروف' }}</span>
                        <span class="text-xs text-[var(--color-ink-500)]">{{ $message->created_at->diffForHumans() }}</span>
                    </div>

                    <p class="mt-4 text-sm leading-8 text-[var(--color-ink-700)]">{{ $message->body }}</p>

                    @if ($message->attachments->isNotEmpty())
                        <div class="forum-attachments mt-5">
                            @foreach ($message->attachments as $attachment)
                                @php($attachmentUrl = asset('storage/'.$attachment->path))

                                @if ($attachment->type->value === 'image')
                                    <a href="{{ $attachmentUrl }}" target="_blank" rel="noreferrer" class="forum-attachment forum-attachment--image">
                                        <img src="{{ $attachmentUrl }}" alt="{{ $attachment->original_name }}" loading="lazy" decoding="async">
                                    </a>
                                @else
                                    <div class="forum-attachment forum-attachment--audio">
                                        <p class="text-xs font-semibold text-[var(--color-brand-700)]">{{ $attachment->original_name }}</p>
                                        <audio controls class="mt-3 w-full">
                                            <source src="{{ $attachmentUrl }}" type="{{ $attachment->mime_type }}">
                                        </audio>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @endif
                </article>
            @endforeach
        </section>

        <section class="panel-tight">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="text-sm font-semibold text-[var(--color-brand-700)]">إضافة رد</p>
                    <p class="mt-2 text-sm leading-8 text-[var(--color-ink-700)]">يمكنك متابعة النقاش بإضافة رد نصي أو صورة أو ملف صوتي.</p>
                </div>
                <a href="{{ route('student.forum.index') }}" class="btn-secondary">العودة إلى الجدار</a>
            </div>

            <form method="POST" action="{{ route('student.forum.reply.store', $thread) }}" enctype="multipart/form-data" class="mt-5 space-y-4">
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
    </section>
</x-layouts.student>
