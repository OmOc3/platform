<x-layouts.student :title="$ticket->subject" heading="تفاصيل التذكرة" subheading="سجل كامل للمراسلات الخاصة بهذه التذكرة مع حالة المتابعة الحالية.">
    <section class="space-y-6">
        <section class="panel-tight">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                <div>
                    <p class="section-kicker">التذكرة #{{ $ticket->id }}</p>
                    <h2 class="mt-2 text-2xl font-bold">{{ $ticket->subject }}</h2>
                    <p class="mt-3 text-sm leading-8 text-[var(--color-ink-700)]">
                        النوع: {{ $ticket->type?->name ?? '—' }} / الفريق: {{ $ticket->team?->name ?? ($ticket->type?->defaultTeam?->name ?? 'غير محدد') }}
                    </p>
                </div>

                <x-admin.status-badge :label="$ticket->status->label()" :tone="$ticket->status->tone()" />
            </div>

            <div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <div class="portal-summary-card">
                    <span class="portal-summary-card__label">عدد الردود</span>
                    <strong class="portal-summary-card__value">{{ $ticket->replies->count() }}</strong>
                </div>
                <div class="portal-summary-card">
                    <span class="portal-summary-card__label">المسؤول الحالي</span>
                    <strong class="portal-summary-card__value">{{ $ticket->assignedAdmin?->name ?? 'غير مسند' }}</strong>
                </div>
                <div class="portal-summary-card">
                    <span class="portal-summary-card__label">آخر نشاط</span>
                    <strong class="portal-summary-card__value">{{ optional($ticket->last_activity_at)->diffForHumans() ?? '—' }}</strong>
                </div>
                <div class="portal-summary-card">
                    <span class="portal-summary-card__label">المتابعة</span>
                    <strong class="portal-summary-card__value">{{ $ticket->status->label() }}</strong>
                </div>
            </div>
        </section>

        <section class="space-y-4">
            @foreach ($ticket->replies as $reply)
                <article @class([
                    'panel-tight',
                    'surface-tone surface-tone--success' => $reply->is_staff_reply,
                ])>
                    <div class="flex flex-wrap items-center gap-3">
                        <x-admin.status-badge :label="$reply->is_staff_reply ? 'رد من فريق الدعم' : 'رسالتك'" :tone="$reply->is_staff_reply ? 'success' : 'neutral'" />
                        <span class="text-xs text-[var(--color-ink-500)]">{{ $reply->author?->name ?? 'غير معروف' }}</span>
                        <span class="text-xs text-[var(--color-ink-500)]">{{ $reply->created_at->diffForHumans() }}</span>
                    </div>

                    <p class="mt-4 text-sm leading-8 text-[var(--color-ink-700)]">{{ $reply->body }}</p>
                </article>
            @endforeach
        </section>

        <section class="panel-tight">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="text-sm font-semibold text-[var(--color-brand-700)]">إضافة رد جديد</p>
                    <p class="mt-2 text-sm leading-8 text-[var(--color-ink-700)]">أضف أي تحديث أو توضيح جديد ليظهر مباشرة داخل التذكرة الحالية.</p>
                </div>
                <a href="{{ route('student.tickets.index') }}" class="btn-secondary">العودة إلى التذاكر</a>
            </div>

            @if ($ticket->status->allowsStudentReply())
                <form method="POST" action="{{ route('student.tickets.reply.store', $ticket) }}" class="mt-5 space-y-4">
                    @csrf
                    <div>
                        <label class="field-label" for="body">نص الرد</label>
                        <textarea id="body" name="body" class="form-textarea" required>{{ old('body') }}</textarea>
                        @error('body') <p class="field-help text-[var(--color-danger)]">{{ $message }}</p> @enderror
                    </div>
                    <button class="btn-primary">إرسال الرد</button>
                </form>
            @else
                <div class="surface-tone surface-tone--success mt-5 rounded-[1.8rem] p-4">
                    <p class="text-sm leading-7 text-[var(--color-ink-700)]">
                        التذكرة في حالة "{{ $ticket->status->label() }}" حاليًا، لذلك تم إيقاف الردود الجديدة عليها.
                    </p>
                </div>
            @endif
        </section>
    </section>
</x-layouts.student>
