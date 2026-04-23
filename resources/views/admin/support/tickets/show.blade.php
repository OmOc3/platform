<x-layouts.admin :title="$ticket->subject" :heading="'التذكرة #'.$ticket->id" subheading="مراجعة تفاصيل التذكرة، تحديث حالتها، إسنادها، ثم الرد على الطالب من نفس الصفحة.">
    <section class="grid gap-6 xl:grid-cols-[1.12fr_0.88fr]">
        <section class="space-y-6">
            <section class="panel-tight">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-semibold text-[var(--color-brand-700)]">{{ $ticket->type?->name ?? 'نوع غير محدد' }}</p>
                        <h2 class="mt-2 text-2xl font-bold">{{ $ticket->subject }}</h2>
                        <p class="mt-2 text-sm text-[var(--color-ink-700)]">
                            {{ $ticket->student?->name ?? 'طالب غير معروف' }} / {{ $ticket->student?->student_number ?? '—' }} / {{ optional($ticket->last_activity_at)->format('Y-m-d H:i') ?: '—' }}
                        </p>
                    </div>
                    <x-admin.status-badge :label="$ticket->status->label()" :tone="$ticket->status->tone()" />
                </div>

                <div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                    <div class="admin-workflow-card">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-ink-500)]">الفريق</p>
                        <p class="mt-3 font-semibold">{{ $ticket->team?->name ?? ($ticket->type?->defaultTeam?->name ?? 'بدون فريق') }}</p>
                    </div>
                    <div class="admin-workflow-card">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-ink-500)]">المسؤول</p>
                        <p class="mt-3 font-semibold">{{ $ticket->assignedAdmin?->name ?? 'غير مسندة' }}</p>
                    </div>
                    <div class="admin-workflow-card">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-ink-500)]">عدد الردود</p>
                        <p class="mt-3 font-semibold">{{ $ticket->replies->count() }}</p>
                    </div>
                    <div class="admin-workflow-card">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-ink-500)]">الحالة الحالية</p>
                        <p class="mt-3 font-semibold">{{ $ticket->status->label() }}</p>
                    </div>
                </div>
            </section>

            <section class="panel-tight">
                <p class="text-sm font-semibold text-[var(--color-brand-700)]">بيانات الطالب</p>
                <div class="mt-5 grid gap-4 md:grid-cols-2">
                    <div class="admin-workflow-card">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-ink-500)]">التواصل</p>
                        <p class="mt-3 font-semibold">{{ $ticket->student?->phone ?: '—' }}</p>
                        <p class="mt-1 text-sm text-[var(--color-ink-600)]">{{ $ticket->student?->parent_phone ?: '—' }}</p>
                    </div>
                    <div class="admin-workflow-card">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-ink-500)]">المتابع والسنتر</p>
                        <p class="mt-3 font-semibold">{{ $ticket->student?->ownerAdmin?->name ?: 'بدون متابع' }}</p>
                        <p class="mt-1 text-sm text-[var(--color-ink-600)]">{{ $ticket->student?->center?->name_ar ?: 'بدون سنتر' }} / {{ $ticket->student?->group?->name_ar ?: 'بدون مجموعة' }}</p>
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
                            <x-admin.status-badge :label="$reply->is_staff_reply ? 'رد إداري' : 'رسالة طالب'" :tone="$reply->is_staff_reply ? 'success' : 'neutral'" />
                            <span class="text-xs text-[var(--color-ink-500)]">{{ $reply->author?->name ?? 'غير معروف' }}</span>
                            <span class="text-xs text-[var(--color-ink-500)]">{{ $reply->created_at->diffForHumans() }}</span>
                        </div>

                        <p class="mt-4 text-sm leading-8 text-[var(--color-ink-700)]">{{ $reply->body }}</p>
                    </article>
                @endforeach
            </section>
        </section>

        <aside class="space-y-6">
            <section class="panel-tight">
                <p class="text-sm font-semibold text-[var(--color-brand-700)]">تحديث الحالة</p>
                <form method="POST" action="{{ route('admin.tickets.status.update', $ticket) }}" class="mt-5 space-y-4">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="field-label" for="status">الحالة</label>
                        <select id="status" name="status" class="form-select" required>
                            @foreach ($statuses as $status)
                                <option value="{{ $status->value }}" @selected(old('status', $ticket->status->value) === $status->value)>{{ $status->label() }}</option>
                            @endforeach
                        </select>
                        @error('status') <p class="field-help text-[var(--color-danger)]">{{ $message }}</p> @enderror
                    </div>
                    <button class="btn-primary w-full">حفظ الحالة</button>
                </form>
            </section>

            <section class="panel-tight">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <p class="text-sm font-semibold text-[var(--color-brand-700)]">الإسناد</p>
                    <form method="POST" action="{{ route('admin.tickets.assignment.auto', $ticket) }}">
                        @csrf
                        <button class="btn-secondary !px-4 !py-2">إسناد تلقائي</button>
                    </form>
                </div>

                <form method="POST" action="{{ route('admin.tickets.assignment.update', $ticket) }}" class="mt-5 space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="field-label" for="support_team_id">فريق الدعم</label>
                        <select id="support_team_id" name="support_team_id" class="form-select">
                            <option value="">بدون فريق</option>
                            @foreach ($teams as $team)
                                <option value="{{ $team->id }}" @selected((string) old('support_team_id', $ticket->support_team_id) === (string) $team->id)>{{ $team->name }}</option>
                            @endforeach
                        </select>
                        @error('support_team_id') <p class="field-help text-[var(--color-danger)]">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="field-label" for="assigned_admin_id">المسؤول</label>
                        <select id="assigned_admin_id" name="assigned_admin_id" class="form-select">
                            <option value="">بدون مسؤول</option>
                            @foreach ($admins as $admin)
                                <option value="{{ $admin->id }}" @selected((string) old('assigned_admin_id', $ticket->assigned_admin_id) === (string) $admin->id)>
                                    {{ $admin->name }} / {{ $admin->supportTeams->pluck('name')->join('، ') ?: 'بدون فرق' }}
                                </option>
                            @endforeach
                        </select>
                        @error('assigned_admin_id') <p class="field-help text-[var(--color-danger)]">{{ $message }}</p> @enderror
                    </div>

                    <button class="btn-secondary w-full">حفظ الإسناد</button>
                </form>
            </section>

            <section class="panel-tight">
                <p class="text-sm font-semibold text-[var(--color-brand-700)]">رد إداري</p>

                @if ($ticket->status->allowsAdminReply())
                    <form method="POST" action="{{ route('admin.tickets.reply', $ticket) }}" class="mt-5 space-y-4">
                        @csrf
                        <div>
                            <label class="field-label" for="body">نص الرد</label>
                            <textarea id="body" name="body" class="form-textarea" required>{{ old('body') }}</textarea>
                            @error('body') <p class="field-help text-[var(--color-danger)]">{{ $message }}</p> @enderror
                        </div>
                        <button class="btn-primary w-full">إرسال الرد</button>
                    </form>
                @else
                    <div class="surface-tone surface-tone--success mt-5 rounded-[1.8rem] p-4">
                        <p class="text-sm leading-7 text-[var(--color-ink-700)]">التذكرة مغلقة حاليًا. أعد فتحها أولًا إذا احتجت لإضافة رد جديد.</p>
                    </div>
                @endif
            </section>

            <section class="panel-tight">
                <p class="text-sm font-semibold text-[var(--color-brand-700)]">مؤشرات التشغيل</p>
                <ul class="mt-4 space-y-3 text-sm leading-7 text-[var(--color-ink-700)]">
                    <li>الإسناد التلقائي يعتمد أولًا على الفريق الافتراضي لنوع التذكرة ثم يختار أقل مسؤول تحميلًا داخل الفريق.</li>
                    <li>رد الإدارة يحوّل التذكرة غالبًا إلى "بانتظار الطالب"، بينما رد الطالب يعيدها إلى "بانتظار الفريق".</li>
                    <li>يمكن ترك التذكرة بلا مسؤول فردي مع الإبقاء على الفريق فقط إذا كان ذلك مناسبًا في هذه المرحلة.</li>
                </ul>
            </section>
        </aside>
    </section>
</x-layouts.admin>
