<x-layouts.student title="تذاكر الدعم" heading="تذاكر الدعم" subheading="قناة خاصة لمتابعة المشاكل التقنية والتنظيمية مع فريق الدعم داخل حسابك.">
    <section class="space-y-6">
        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <x-student.summary-card label="إجمالي التذاكر" :value="$overview['total']" description="كل التذاكر المرتبطة بحسابك" />
            <x-student.summary-card label="نشطة الآن" :value="$overview['active']" description="تذاكر ما زالت تحتاج متابعة أو رد" />
            <x-student.summary-card label="في انتظار رد" :value="$overview['waiting']" description="تذاكر تنتظر رد الطالب أو الفريق" />
            <x-student.summary-card label="تم حلها" :value="$overview['resolved']" description="تذاكر وصلت إلى حل واضح" />
        </section>

        <div class="grid gap-6 xl:grid-cols-[0.92fr_1.08fr]">
            <aside class="panel-tight">
                <p class="section-kicker">فتح تذكرة جديدة</p>
                <h2 class="mt-2 text-2xl font-bold">صف المشكلة مرة واحدة، وتابع الردود في نفس المسار.</h2>
                <p class="mt-3 text-sm leading-8 text-[var(--color-ink-700)]">
                    استخدم التذاكر للمشاكل التقنية أو طلبات المتابعة الخاصة بحسابك. إذا كانت الرسالة أقرب لشكوى أو اقتراح عام، يمكنك أيضًا استخدام صفحة الشكاوى والاقتراحات.
                </p>

                <form method="POST" action="{{ route('student.tickets.store') }}" class="mt-6 space-y-4">
                    @csrf

                    <div>
                        <label class="field-label" for="support_ticket_type_id">نوع التذكرة</label>
                        <select id="support_ticket_type_id" name="support_ticket_type_id" class="form-select" required>
                            <option value="">اختر نوع التذكرة</option>
                            @foreach ($ticketTypes as $type)
                                <option value="{{ $type->id }}" @selected((string) old('support_ticket_type_id') === (string) $type->id)>
                                    {{ $type->name }}{{ $type->defaultTeam ? ' / '.$type->defaultTeam->name : '' }}
                                </option>
                            @endforeach
                        </select>
                        @error('support_ticket_type_id') <p class="field-help text-[var(--color-danger)]">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="field-label" for="subject">عنوان مختصر</label>
                        <input id="subject" name="subject" value="{{ old('subject') }}" class="form-input" required>
                        @error('subject') <p class="field-help text-[var(--color-danger)]">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="field-label" for="body">تفاصيل المشكلة</label>
                        <textarea id="body" name="body" class="form-textarea" required>{{ old('body') }}</textarea>
                        @error('body') <p class="field-help text-[var(--color-danger)]">{{ $message }}</p> @enderror
                    </div>

                    <button class="btn-primary">إنشاء التذكرة</button>
                </form>

                <div class="surface-tone surface-tone--success mt-6 rounded-[1.8rem] p-4">
                    <p class="text-sm font-semibold text-[var(--color-brand-700)]">قنوات دعم أخرى</p>
                    <div class="mt-4 flex flex-wrap gap-2">
                        <a href="{{ route('student.complaints.index') }}" class="btn-secondary !px-4 !py-2">الشكاوى والاقتراحات</a>
                        <a href="{{ route('student.forum.index') }}" class="btn-secondary !px-4 !py-2">ملتقى الأسئلة</a>
                    </div>
                </div>
            </aside>

            <section class="table-shell">
                <div class="flex flex-col gap-4 px-5 py-5 lg:flex-row lg:items-start lg:justify-between">
                    <div>
                        <p class="section-kicker">السجل الحالي</p>
                        <h2 class="mt-2 text-lg font-bold">كل تذاكرك المفتوحة والمغلقة</h2>
                        <p class="mt-2 text-sm leading-7 text-[var(--color-ink-700)]">افتح أي تذكرة لمراجعة التسلسل الكامل والرد داخل نفس الصفحة.</p>
                    </div>
                    <a href="{{ route('student.complaints.index') }}" class="btn-secondary">فتح الشكاوى والاقتراحات</a>
                </div>

                @if ($tickets->isEmpty())
                    <div class="px-5 pb-5">
                        <x-student.empty-state title="لا توجد تذاكر بعد" description="أنشئ أول تذكرة دعم لتظهر هنا مع الفريق والحالة والتحديثات اللاحقة." />
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>التذكرة</th>
                                    <th>النوع</th>
                                    <th>الفريق</th>
                                    <th>الحالة</th>
                                    <th>آخر نشاط</th>
                                    <th>إجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($tickets as $ticket)
                                    <tr>
                                        <td>
                                            <p class="font-semibold">#{{ $ticket->id }} - {{ $ticket->subject }}</p>
                                            <p class="mt-2 text-xs text-[var(--color-ink-500)]">
                                                {{ $ticket->replies_count }} رد
                                                @if ($ticket->assignedAdmin)
                                                    / المسؤول: {{ $ticket->assignedAdmin->name }}
                                                @endif
                                            </p>
                                        </td>
                                        <td>{{ $ticket->type?->name ?? '—' }}</td>
                                        <td>{{ $ticket->team?->name ?? ($ticket->type?->defaultTeam?->name ?? 'بدون فريق') }}</td>
                                        <td><x-admin.status-badge :label="$ticket->status->label()" :tone="$ticket->status->tone()" /></td>
                                        <td>{{ optional($ticket->last_activity_at)->diffForHumans() ?? '—' }}</td>
                                        <td><a href="{{ route('student.tickets.show', $ticket) }}" class="btn-primary !px-4 !py-2">فتح</a></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="px-5 py-4">
                        {{ $tickets->links() }}
                    </div>
                @endif
            </section>
        </div>
    </section>
</x-layouts.student>
