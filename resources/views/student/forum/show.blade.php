<x-layouts.student :title="$thread->title" :heading="$thread->title" subheading="تفاصيل الموضوع، الردود المتسلسلة، والمرفقات المرتبطة بكل رسالة.">
    <section class="space-y-6">
        <section class="panel-tight">
            <div class="flex flex-wrap items-center gap-3">
                <x-admin.status-badge :label="$thread->status->value" :tone="$thread->status->value === 'answered' ? 'success' : ($thread->status->value === 'closed' ? 'warning' : 'neutral')" />
                <span class="text-xs text-[var(--color-ink-500)]">{{ $thread->student?->name }}</span>
                <span class="text-xs text-[var(--color-ink-500)]">{{ optional($thread->last_activity_at)->diffForHumans() }}</span>
            </div>

            <div class="mt-6 space-y-4">
                @foreach ($thread->messages as $message)
                    <article class="rounded-[1.8rem] border border-[color-mix(in_oklch,var(--color-brand-200)_70%,white)] px-5 py-5">
                        <div class="flex flex-wrap items-center gap-3">
                            <x-admin.status-badge :label="$message->is_staff_reply ? 'رد إداري' : 'رسالة طالب'" :tone="$message->is_staff_reply ? 'success' : 'neutral'" />
                            <span class="text-xs text-[var(--color-ink-500)]">{{ $message->author?->name ?? 'غير معروف' }}</span>
                            <span class="text-xs text-[var(--color-ink-500)]">{{ $message->created_at->diffForHumans() }}</span>
                        </div>

                        <p class="mt-4 text-sm leading-8 text-[var(--color-ink-700)]">{{ $message->body }}</p>

                        @if ($message->attachments->isNotEmpty())
                            <div class="mt-4 flex flex-wrap gap-2">
                                @foreach ($message->attachments as $attachment)
                                    <a href="{{ asset('storage/'.$attachment->path) }}" target="_blank" class="status-pill bg-[var(--color-brand-50)] text-[var(--color-brand-700)]">
                                        {{ $attachment->original_name }}
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </article>
                @endforeach
            </div>
        </section>

        <section class="panel-tight">
            <p class="text-sm font-semibold text-[var(--color-brand-700)]">إضافة رد</p>
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
