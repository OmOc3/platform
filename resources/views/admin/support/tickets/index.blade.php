<x-layouts.admin title="تذاكر الدعم" heading="تذاكر الدعم" subheading="متابعة التذاكر الخاصة، توزيعها على الفرق، والرد عليها من لوحة الإدارة.">
    <section class="admin-metric-grid">
        <x-admin.metric-card label="كل التذاكر" :value="$overview['total']" description="إجمالي التذاكر المسجلة داخل المنصة." />
        <x-admin.metric-card label="تحتاج متابعة" :value="$overview['active']" description="تذاكر ما زالت ضمن دورة العمل النشطة." />
        <x-admin.metric-card label="بانتظار الطالب" :value="$overview['waiting_customer']" description="تذاكر تم الرد عليها وتنتظر استكمالًا من الطالب." />
        <x-admin.metric-card label="غير مسندة" :value="$overview['unassigned']" description="تذاكر لم يتم ربطها بمسؤول محدد بعد." />
    </section>

    <x-admin.table-shell title="قائمة تذاكر الدعم" description="ابحث باسم الطالب أو موضوع التذكرة أو محتوى الرسائل، ثم صفِّ حسب الفريق أو النوع أو حالة الإسناد.">
        <x-slot:filters>
            <form method="GET" class="grid gap-3 xl:grid-cols-[1.2fr_220px_220px_220px_200px_auto]">
                <input type="search" name="search" value="{{ request('search') }}" class="form-input" placeholder="ابحث بعنوان التذكرة أو اسم الطالب" aria-label="ابحث في التذاكر">
                <select name="type" class="form-select">
                    <option value="">كل الأنواع</option>
                    @foreach ($types as $type)
                        <option value="{{ $type->id }}" @selected((string) request('type') === (string) $type->id)>{{ $type->name }}</option>
                    @endforeach
                </select>
                <select name="team" class="form-select">
                    <option value="">كل الفرق</option>
                    @foreach ($teams as $team)
                        <option value="{{ $team->id }}" @selected((string) request('team') === (string) $team->id)>{{ $team->name }}</option>
                    @endforeach
                </select>
                <select name="status" class="form-select">
                    <option value="">كل الحالات</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
                    @endforeach
                </select>
                <select name="assignment" class="form-select">
                    <option value="">كل التذاكر</option>
                    <option value="mine" @selected(request('assignment') === 'mine')>مسندة لي</option>
                    <option value="unassigned" @selected(request('assignment') === 'unassigned')>غير مسندة</option>
                </select>
                <button class="btn-secondary">تطبيق</button>
            </form>
        </x-slot:filters>

        <table class="data-table">
            <thead>
                <tr>
                    <th>التذكرة</th>
                    <th>الطالب</th>
                    <th>النوع</th>
                    <th>الفريق / المسؤول</th>
                    <th>الحالة</th>
                    <th>آخر نشاط</th>
                    <th>إجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($tickets as $ticket)
                    <tr>
                        <td>
                            <p class="font-semibold">#{{ $ticket->id }} - {{ $ticket->subject }}</p>
                            <p class="mt-2 text-xs text-[var(--color-ink-500)]">
                                {{ str($ticket->latestReply?->body)->limit(90) }}
                            </p>
                        </td>
                        <td>
                            <p class="font-semibold">{{ $ticket->student?->name ?? '—' }}</p>
                            <p class="mt-1 text-xs text-[var(--color-ink-500)]">{{ $ticket->student?->student_number ?? '—' }}</p>
                        </td>
                        <td>{{ $ticket->type?->name ?? '—' }}</td>
                        <td>
                            <p class="font-semibold">{{ $ticket->team?->name ?? 'بدون فريق' }}</p>
                            <p class="mt-1 text-xs text-[var(--color-ink-500)]">{{ $ticket->assignedAdmin?->name ?? 'غير مسندة' }}</p>
                        </td>
                        <td><x-admin.status-badge :label="$ticket->status->label()" :tone="$ticket->status->tone()" /></td>
                        <td>{{ optional($ticket->last_activity_at)->format('Y-m-d H:i') ?: '—' }}</td>
                        <td><a href="{{ route('admin.tickets.show', $ticket) }}" class="btn-secondary !px-4 !py-2">فتح</a></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-[var(--color-ink-500)]">لا توجد تذاكر مطابقة للفلاتر الحالية.</td></tr>
                @endforelse
            </tbody>
        </table>

        <div class="px-5 py-4">{{ $tickets->links() }}</div>
    </x-admin.table-shell>
</x-layouts.admin>
