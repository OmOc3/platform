<x-layouts.admin title="الطلاب" heading="الطلاب" subheading="سجل متابعة موحد للطالب يشمل الحالة، المالك الإداري، السنتر، والمجموعة.">
    <section class="admin-metric-grid">
        <x-admin.metric-card label="كل الطلاب" :value="$overview['total']" description="جميع الحسابات الطلابية داخل المنصة." />
        <x-admin.metric-card label="طلاب مشتركون" :value="$overview['subscribed']" description="حسابات مفعلة وقابلة للوصول الكامل." />
        <x-admin.metric-card label="قيد المراجعة" :value="$overview['pending']" description="طلبات تحتاج قرارًا إداريًا أو متابعة أولية." />
        <x-admin.metric-card label="مسندة لمتابع" :value="$overview['assigned']" description="طلاب مرتبطون بمالك إداري أو مسؤول متابعة." />
    </section>

    <x-admin.table-shell title="قائمة الطلاب" description="ابحث وصَفِّ حسب الحالة أو المصدر أو السنتر أو المالك الإداري، ثم انتقل إلى صفحة الطالب لمتابعته.">
        <x-slot:actions>
            <a href="{{ route('admin.students.index', array_merge(request()->query(), ['export' => 'csv'])) }}" class="btn-secondary">تصدير CSV</a>
        </x-slot:actions>

        <x-slot:filters>
            <form method="GET" class="grid gap-3 lg:grid-cols-8">
                <input type="search" name="search" value="{{ request('search') }}" class="form-input lg:col-span-2" placeholder="الاسم أو البريد أو الهاتف أو الرقم" aria-label="ابحث في الطلاب">
                <select name="status" class="form-select">
                    <option value="">كل الحالات</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
                    @endforeach
                </select>
                <select name="source_type" class="form-select">
                    <option value="">كل المصادر</option>
                    @foreach ($sourceTypes as $sourceType)
                        <option value="{{ $sourceType->value }}" @selected(request('source_type') === $sourceType->value)>{{ $sourceType->label() }}</option>
                    @endforeach
                </select>
                <select name="grade_id" class="form-select">
                    <option value="">كل الصفوف</option>
                    @foreach ($grades as $grade)
                        <option value="{{ $grade->id }}" @selected((string) request('grade_id') === (string) $grade->id)>{{ $grade->name_ar }}</option>
                    @endforeach
                </select>
                <select name="center_id" class="form-select">
                    <option value="">كل السناتر</option>
                    @foreach ($centers as $center)
                        <option value="{{ $center->id }}" @selected((string) request('center_id') === (string) $center->id)>{{ $center->name_ar }}</option>
                    @endforeach
                </select>
                <select name="owner_admin_id" class="form-select">
                    <option value="">كل المتابعين</option>
                    @foreach ($owners as $owner)
                        <option value="{{ $owner->id }}" @selected((string) request('owner_admin_id') === (string) $owner->id)>{{ $owner->name }}</option>
                    @endforeach
                </select>
                <label class="flex items-center gap-2 text-sm text-[var(--color-ink-700)]">
                    <input type="checkbox" name="is_azhar" value="1" class="h-4 w-4 rounded border-[var(--color-brand-200)] text-[var(--color-brand-700)]" @checked(request()->boolean('is_azhar'))>
                    أزهري فقط
                </label>
                <button class="btn-secondary lg:col-span-1">تطبيق</button>
            </form>
        </x-slot:filters>

        <table class="data-table">
            <thead>
                <tr>
                    <th>الطالب</th>
                    <th>الرقم</th>
                    <th>المسار الدراسي</th>
                    <th>المصدر</th>
                    <th>المتابعة</th>
                    <th>الحالة</th>
                    <th>إجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($students as $row)
                    <tr>
                        <td>
                            <p class="font-semibold">{{ $row->name }}</p>
                            <p class="mt-1 text-xs text-[var(--color-ink-500)]">{{ $row->email }}</p>
                        </td>
                        <td>{{ $row->student_number }}</td>
                        <td>
                            <p>{{ $row->grade?->name_ar ?: '—' }}</p>
                            <p class="mt-1 text-xs text-[var(--color-ink-500)]">{{ $row->track?->name_ar ?: 'عام' }}</p>
                        </td>
                        <td>{{ $row->source_type?->label() ?: '—' }}</td>
                        <td>
                            <p>{{ $row->ownerAdmin?->name ?: 'بدون تعيين' }}</p>
                            <p class="mt-1 text-xs text-[var(--color-ink-500)]">{{ $row->center?->name_ar ?: 'بدون سنتر' }} / {{ $row->group?->name_ar ?: 'بدون مجموعة' }}</p>
                        </td>
                        <td>
                            <x-admin.status-badge
                                :label="$row->status->label()"
                                :tone="$row->status === \App\Shared\Enums\StudentStatus::Subscribed ? 'success' : ($row->status === \App\Shared\Enums\StudentStatus::Pending ? 'warning' : 'danger')"
                            />
                        </td>
                        <td><a href="{{ route('admin.students.edit', $row) }}" class="btn-secondary !px-4 !py-2">متابعة</a></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-[var(--color-ink-500)]">لا يوجد طلاب مطابقون للفلاتر الحالية.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="px-5 py-4">
            {{ $students->links() }}
        </div>
    </x-admin.table-shell>
</x-layouts.admin>
